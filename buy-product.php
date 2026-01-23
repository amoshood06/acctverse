<?php
require_once "./db/db.php";
require_once "./flash.php";

try {
    $pdo->beginTransaction();
    // Check if user is logged in
    if (!isset($_SESSION['user']['id'])) {
        set_flash("error", "Please log in to complete your purchase.");
        header("Location: login.php");
        exit();
    }

    // Validate POST data
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        set_flash("error", "Invalid request: Missing product_id or quantity");
        header("Location: index.php"); // Redirect to a safe page
        exit();
    }

    $user_id = $_SESSION['user']['id'];
    $quantity = (int)$_POST['quantity']; // Use quantity from POST

    // Validate quantity
    if ($quantity < 1) {
        set_flash("error", "Invalid quantity");
        header("Location: index.php");
        exit();
    }

    $product_ids_to_purchase = [];
    $category = '';
    $sub_category = '';
    $price_per_unit = 0;
    $total_amount = 0;


    if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
        // This is a multi-item purchase from confirm-multi-purchase.php
        $product_ids_to_purchase = array_map('intval', $_POST['product_ids']);
        // Ensure quantity matches the number of IDs provided
        if (count($product_ids_to_purchase) !== $quantity) {
             set_flash("error", "Mismatch in quantity and provided product IDs.");
             header("Location: index.php");
             exit();
        }

        $sub_category = $_POST['sub_category'];
        $category = $_POST['category'];
        $total_amount = (float)$_POST['total_amount']; // Directly use total_amount from confirmation page

        // Fetch product details for the specific IDs to get product_name and price
        $placeholders = implode(',', array_fill(0, count($product_ids_to_purchase), '?'));
        $stmt = $pdo->prepare("SELECT id, product_name, price, category, sub_category, image FROM products WHERE id IN ($placeholders) AND sub_category = ? AND category = ? FOR UPDATE");
        $stmt->execute(array_merge($product_ids_to_purchase, [$sub_category, $category]));
        $products_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($products_data) !== $quantity) {
            set_flash("error", "Some selected products are no longer available or do not match the criteria.");
            header("Location: index.php");
            exit();
        }

        // Re-calculate total_amount as a security check against tampering
        $recalculated_total_amount = 0;
        $deleted_product_names = [];
        $deleted_product_images = [];

        foreach ($products_data as $p) {
            $recalculated_total_amount += $p['price'];
            $deleted_product_names[] = $p['product_name'];
            $deleted_product_images[] = $p['image'];
            $price_per_unit = $p['price']; // Assuming all units in a sub_category have the same price.
        }

        if (abs($recalculated_total_amount - $total_amount) > 0.01) { // Floating point comparison
            set_flash("error", "Total amount mismatch. Please try again.");
            header("Location: index.php");
            exit();
        }

        // Use the category and sub_category from POST, and price_per_unit from first product
        // The $_POST['product_id'] will serve as the representative ID for the order record
        $product_id = (int)$_POST['product_id'];

    } else {
        // This is a single-item purchase (original flow)
        $product_id = (int)$_POST['product_id'];

        // Fetch information about the "type" of product (sub_category) using the example product_id
        $stmt = $pdo->prepare("SELECT category, sub_category, price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product_template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product_template) {
            set_flash("error", "Product type not found.");
            header("Location: index.php");
            exit();
        }

        $category = $product_template['category'];
        $sub_category = $product_template['sub_category'];
        $price_per_unit = (float)$product_template['price'];
        $total_amount = $price_per_unit * $quantity;

        // Check available stock for the sub_category
        $stmt = $pdo->prepare("SELECT COUNT(*) AS available_stock FROM products WHERE category = ? AND sub_category = ?");
        $stmt->execute([$category, $sub_category]);
        $stock_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $available_stock = (int)$stock_data['available_stock'];

        if ($available_stock < $quantity) {
            set_flash("error", "Insufficient stock for " . htmlspecialchars($sub_category) . ". Available: " . $available_stock);
            header("Location: index.php");
            exit();
        }

        // Get IDs of 'quantity' products to be removed from stock
        // Using FOR UPDATE to lock these rows during the transaction
        $stmt = $pdo->prepare("SELECT id, product_name, image FROM products WHERE category = ? AND sub_category = ? LIMIT ? FOR UPDATE");
        $stmt->bindValue(1, $category);
        $stmt->bindValue(2, $sub_category);
        $stmt->bindValue(3, $quantity, PDO::PARAM_INT);
        $stmt->execute();
        $products_to_delete = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($products_to_delete) < $quantity) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            set_flash("error", "Failed to secure required number of products. Please try again.");
            header("Location: index.php");
            exit();
        }

        foreach ($products_to_delete as $p) {
            $product_ids_to_purchase[] = $p['id'];
            $deleted_product_names[] = $p['product_name'];
            $deleted_product_images[] = $p['image'];
        }
    }


    // Get user wallet balance and lock the row
    $stmt = $pdo->prepare("SELECT balance, email, full_name FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        die("User not found"); // Should not happen if session check passed
    }

    $balance = (float)$userData['balance'];

    // Check if user has sufficient balance (re-check if not from confirmation page or for single item)
    // For multi-item, total_amount was already checked on confirm-multi-purchase.php, but this is a final server-side check.
    if ($balance < $total_amount) {
        set_flash("error", "Insufficient balance to complete the purchase. Current balance: ₦" . number_format($balance, 2));
        header("Location: ./user/create-wallet.php"); // Redirect to a page where user can add funds
        exit();
    }

    // Deduct balance from user account
    $newBalance = $balance - $total_amount;
    $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->execute([$newBalance, $user_id]);
    
    // Delete the selected products from stock
    $placeholders = implode(',', array_fill(0, count($product_ids_to_purchase), '?'));
    $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids_to_purchase);

    // Prepare information for the order record (aggregate product name for multiple items)
    $product_name_for_order = "{$sub_category} (x{$quantity})";
    // Using the first product image as representative, or null if none
    $product_image_for_order = !empty($deleted_product_images) ? $deleted_product_images[0] : null;

    // Insert order record
    // Note: product_id in orders table will now store the representative ID for the sub_category, not an actual purchased product ID
    $stmt = $pdo->prepare(" 
        INSERT INTO orders (user_id, product_id, product_name, image, price, quantity, total_amount, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', NOW())
    ");
    $stmt->execute([$user_id, $product_id, $product_name_for_order, $product_image_for_order, $price_per_unit, $quantity, $total_amount]);

    // Also insert into transactions table for a complete financial record
    $stmt_trans = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'purchase', ?, ?, 'completed')");
    $stmt_trans->execute([$user_id, $total_amount, "Purchase of " . $quantity . " x " . $product_name_for_order]);

    $order_id = $pdo->lastInsertId();

    // Send order confirmation email
    $user_email = $userData['email'];
    $user_full_name = $userData['full_name'];

    $subject = "Your AcctVerse Order Confirmation (#" . $order_id . ")";
    $message = "
        Hello " . htmlspecialchars($user_full_name) . ",<br><br>
        Thank you for your order. Here are the details:<br><br>
        <strong>Order ID:</strong> #" . $order_id . "<br>
        <strong>Product:</strong> " . htmlspecialchars($product_name_for_order) . "<br>
        <strong>Quantity:</strong> " . htmlspecialchars($quantity) . "<br>
        <strong>Total Amount:</strong> ₦" . number_format($total_amount, 2) . "<br><br>
        You can view your order history in your dashboard.<br><br>
        Regards,<br>The AcctVerse Team
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: noreply@acctverse.com\r\n";
    mail($user_email, $subject, $message, $headers);


    // If all steps are successful, commit the transaction
    $pdo->commit();

    // Store product details in session to display on success page
    $_SESSION['purchased_product_details'] = "Purchased " . $quantity . " units of " . htmlspecialchars($sub_category) . ".";
    $_SESSION['purchased_product_name'] = htmlspecialchars($sub_category);

    // Redirect to success page
    header("Location: order-success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    // If any step fails, roll back all database changes
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Buy Product Error: " . $e->getMessage());
    set_flash("error", "An error occurred while processing your order. Please try again.");
    header("Location: index.php"); // Redirect to a safe page
    exit();
}
?>