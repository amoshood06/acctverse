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
    header("Location: products.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$selected_products = [];
$total_order_amount = 0;

// Filter and validate selected products from the form
if (isset($_POST['products']) && is_array($_POST['products'])) {
    foreach ($_POST['products'] as $product_id => $quantity) {
        $product_id = filter_var($product_id, FILTER_VALIDATE_INT);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

        if ($product_id > 0 && $quantity > 0) {
            $selected_products[] = ['product_id' => $product_id, 'quantity' => $quantity];
        }
    }
}

if (empty($selected_products)) {
    set_flash("error", "No products selected for purchase.");
    header("Location: products.php");
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

    $products_to_order = []; // To store product details for later insertion

    // First pass: Validate stock and calculate total amount
    foreach ($selected_products as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        // Fetch product details and lock the row
        $stmt_product = $pdo->prepare("SELECT product_name, price, stock FROM products WHERE id = ? FOR UPDATE");
        $stmt_product->execute([$product_id]);
        $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product with ID {$product_id} not found.");
        }
        if ($product['stock'] < $quantity) {
            throw new Exception("Insufficient stock for '{$product['product_name']}'. Available: {$product['stock']}, Requested: {$quantity}.");
        }

        $item_total_cost = $product['price'] * $quantity;
        $total_order_amount += $item_total_cost;
        $products_to_order[] = array_merge($item, $product, ['item_total_cost' => $item_total_cost]);
    }

    // Check if user has sufficient balance for the entire order
    if ($user_balance < $total_order_amount) {
        throw new Exception("Insufficient balance. Your current balance is ₦" . number_format($user_balance, 2) . ", but the total order is ₦" . number_format($total_order_amount, 2) . ".");
    }

    // Deduct total amount from user's balance
    $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$total_order_amount, $user_id]);

    // Second pass: Update stock and create order/transaction records
    foreach ($products_to_order as $item) {
        $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
        $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity, total_amount, status) VALUES (?, ?, ?, ?, ?)")->execute([$user_id, $item['product_id'], $item['quantity'], $item['item_total_cost'], 'completed']);
        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'purchase', ?, ?, ?)")->execute([$user_id, $item['item_total_cost'], "Purchase of {$item['quantity']} units of {$item['product_name']}", 'completed']);
    }

    // -----------------------------------------------------------
    // ✅ START: TIERED REFERRAL COMMISSION LOGIC
    // -----------------------------------------------------------
    // Find out who referred the current user (the buyer)
    $stmt_referrer = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
    $stmt_referrer->execute([$user_id]);
    $referrer_data = $stmt_referrer->fetch(PDO::FETCH_ASSOC);

    if ($referrer_data && !empty($referrer_data['referred_by'])) {
        $referrer_id = $referrer_data['referred_by'];

        // Count how many total referrals this referrer has
        $stmt_ref_count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
        $stmt_ref_count->execute([$referrer_id]);
        $referral_count = $stmt_ref_count->fetchColumn();

        // Get the correct commission rate from the database based on the number of referrals
        $stmt_tier = $pdo->prepare(
            "SELECT commission_rate FROM referral_tiers WHERE min_referrals <= ? ORDER BY min_referrals DESC LIMIT 1"
        );
        $stmt_tier->execute([$referral_count]);
        $commission_rate = $stmt_tier->fetchColumn();
        
        // Calculate and award the commission
        if ($commission_rate > 0) {
            $commission_amount = $total_order_amount * $commission_rate;

            // Add commission to the referrer's earnings balance
            $stmt_award = $pdo->prepare("UPDATE users SET referral_earnings = referral_earnings + ? WHERE id = ?");
            $stmt_award->execute([$commission_amount, $referrer_id]);

            // Log the commission transaction for the referrer
            $buyer_username = $_SESSION['user']['username'] ?? 'user #'.$user_id;
            $stmt_log_commission = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'referral_earning', ?, ?, 'completed')");
            $stmt_log_commission->execute([$referrer_id, $commission_amount, "Commission from {$buyer_username}'s purchase"]);
        }
    }
    // -----------------------------------------------------------
    // ✅ END: TIERED REFERRAL COMMISSION LOGIC
    // -----------------------------------------------------------

    $pdo->commit();
    set_flash("success", "Your order has been placed successfully!");
    header("Location: order-history.php");

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash("error", "Order failed: " . $e->getMessage());
    header("Location: products.php");
}

exit();