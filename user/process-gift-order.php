<?php
// Ensure session is started for $_SESSION variables
require_once "../db/db.php";
require_once "../flash.php";

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    set_flash("error", "You must be logged in to place an order.");
    header("Location: ../login.php");
    exit;
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: gift-products.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$selected_product_ids = [];

// Filter and validate selected product IDs from the form
if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
    foreach ($_POST['product_ids'] as $product_id) {
        $product_id = filter_var($product_id, FILTER_VALIDATE_INT);
        if ($product_id > 0) {
            $selected_product_ids[] = $product_id;
        }
    }
}

if (empty($selected_product_ids)) {
    set_flash("error", "No products selected for purchase.");
    header("Location: gift-products.php");
    exit;
}

$pdo->beginTransaction();

try {
    // Fetch user's current balance and lock the row for the transaction
    $stmt_user = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
    $stmt_user->execute([$user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        throw new Exception("User not found.");
    }
    $user_balance = $user_data['balance'];

    $total_order_amount = 0;
    $products_to_order = [];

    // First pass: Validate products and calculate total amount
    foreach ($selected_product_ids as $product_id) {
        // Fetch product details and lock the row
        $stmt_product = $pdo->prepare("SELECT id, name, price, details, image, stock FROM gift_products WHERE id = ? AND stock > 0 FOR UPDATE");
        $stmt_product->execute([$product_id]);
        $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            // This could happen if another user bought the product in the meantime or it's out of stock
            throw new Exception("One of the selected products (ID: {$product_id}) is no longer available or out of stock.");
        }

        $total_order_amount += $product['price'];
        $products_to_order[] = $product;
    }

    // Check if user has sufficient balance for the entire order
    if ($user_balance < $total_order_amount) {
        throw new Exception("Insufficient balance. Your current balance is ₦" . number_format($user_balance, 2) . ", but the total order is ₦" . number_format($total_order_amount, 2) . ".");
    }

    // Deduct total amount from user's balance
    $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$total_order_amount, $user_id]);

    $purchased_product_details = [];

    // Second pass: Decrement stock and create order/transaction records
    foreach ($products_to_order as $product) {
        // Decrement the product stock
        $pdo->prepare("UPDATE gift_products SET stock = stock - 1 WHERE id = ?")->execute([$product['id']]);

        // Insert into orders table
        $pdo->prepare("INSERT INTO orders (user_id, product_id, product_name, image, price, quantity, total_amount, admin_note, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$user_id, $product['id'], $product['name'], $product['image'], $product['price'], 1, $product['price'], $product['details'], 'completed']);
        
        $order_id = $pdo->lastInsertId();

        // Insert into transactions table
        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'purchase', ?, ?, 'completed')")
            ->execute([$user_id, $product['price'], "Purchase of gift: {$product['name']}"]);

        // Store details for the success page
        $purchased_product_details[] = [
            'name' => $product['name'],
            'details' => $product['details']
        ];
    }
    
    $_SESSION['purchased_products'] = $purchased_product_details;

    // Referral commission logic (copied from process-order.php)
    $stmt_referrer = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
    $stmt_referrer->execute([$user_id]);
    $referrer_data = $stmt_referrer->fetch(PDO::FETCH_ASSOC);

    if ($referrer_data && !empty($referrer_data['referred_by'])) {
        $referrer_id = $referrer_data['referred_by'];

        $stmt_ref_count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
        $stmt_ref_count->execute([$referrer_id]);
        $referral_count = $stmt_ref_count->fetchColumn();

        $stmt_tier = $pdo->prepare(
            "SELECT commission_rate FROM referral_tiers WHERE min_referrals <= ? ORDER BY min_referrals DESC LIMIT 1"
        );
        $stmt_tier->execute([$referral_count]);
        $commission_rate = $stmt_tier->fetchColumn();
        
        if ($commission_rate > 0) {
            $commission_amount = $total_order_amount * $commission_rate;

            $stmt_award = $pdo->prepare("UPDATE users SET referral_earnings = referral_earnings + ? WHERE id = ?");
            $stmt_award->execute([$commission_amount, $referrer_id]);

            $buyer_username = $_SESSION['user']['username'] ?? 'user #'.$user_id;
            $stmt_log_commission = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'referral_earning', ?, ?, 'completed')");
            $stmt_log_commission->execute([$referrer_id, $commission_amount, "Commission from {$buyer_username}'s gift purchase"]);
        }
    }

    $pdo->commit();
    set_flash("success", "Your gift order has been placed successfully!");
    header("Location: ../order-success.php");

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash("error", "Order failed: " . $e->getMessage());
    header("Location: gift-products.php");
}

exit();