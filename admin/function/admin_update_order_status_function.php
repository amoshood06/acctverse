<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_update_order_status_function.php

/**
 * Create order status history table if it doesn't exist
 */
function create_order_status_table($pdo) {
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS order_status_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                old_status VARCHAR(50),
                new_status VARCHAR(50) NOT NULL,
                changed_by INT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_order_id (order_id),
                INDEX idx_created_at (created_at)
            )
        ");

        // Ensure orders table has status column
        $pdo->query("
            ALTER TABLE orders 
            ADD COLUMN IF NOT EXISTS status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending'
        ");

        return ['success' => true, 'message' => 'Order status tables created'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get order by ID
 */
function get_order_by_id($pdo, $order_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email, u.phone, u.full_name, u.address
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get order items
 */
function get_order_items($pdo, $order_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM order_items 
            WHERE order_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update order status
 */
function update_order_status($pdo, $order_id, $new_status, $admin_id = null, $notes = '') {
    try {
        create_order_status_table($pdo);

        // Validate status
        $allowed_status = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        if (!in_array($new_status, $allowed_status)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        // Get current order
        $order = get_order_by_id($pdo, $order_id);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $old_status = $order['status'];

        // Prevent invalid status transitions
        $invalid_transitions = [
            'delivered' => ['pending', 'processing'],
            'cancelled' => ['delivered', 'refunded'],
            'refunded' => ['pending', 'processing']
        ];

        if (isset($invalid_transitions[$new_status]) && in_array($old_status, $invalid_transitions[$new_status])) {
            return ['success' => false, 'message' => 'Invalid status transition from ' . $old_status . ' to ' . $new_status];
        }

        // Update order status
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$new_status, $order_id]);

        // Record status change in history
        $historyStmt = $pdo->prepare("
            INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $historyStmt->execute([$order_id, $old_status, $new_status, $admin_id, $notes]);

        return [
            'success' => true,
            'message' => 'Order status updated from ' . $old_status . ' to ' . $new_status,
            'old_status' => $old_status,
            'new_status' => $new_status
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get order status history
 */
function get_order_status_history($pdo, $order_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT osh.*, u.username, u.full_name
            FROM order_status_history osh
            LEFT JOIN users u ON osh.changed_by = u.id
            WHERE osh.order_id = ?
            ORDER BY osh.created_at DESC
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Bulk update order status
 */
function bulk_update_order_status($pdo, $order_ids, $new_status, $admin_id = null, $notes = '') {
    try {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($order_ids as $order_id) {
            $result = update_order_status($pdo, $order_id, $new_status, $admin_id, $notes);
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = 'Order ' . $order_id . ': ' . $result['message'];
            }
        }

        return $results;
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get pending orders
 */
function get_pending_orders($pdo, $page = 1, $limit = 10) {
    try {
        $offset = ($page - 1) * $limit;

        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email, COUNT(oi.id) as items_count, SUM(oi.subtotal) as total_amount
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.status = 'pending'
            GROUP BY o.id
            ORDER BY o.created_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['orders' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

/**
 * Get orders by status
 */
function get_orders_by_status($pdo, $status, $page = 1, $limit = 10, $search = '') {
    try {
        $offset = ($page - 1) * $limit;

        $query = "
            SELECT o.*, u.username, u.email, COUNT(oi.id) as items_count, SUM(oi.subtotal) as total_amount
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.status = ?
        ";
        $params = [$status];

        if (!empty($search)) {
            $query .= " AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total
        $countQuery = "SELECT COUNT(*) as total FROM orders WHERE status = ?";
        $countParams = [$status];

        if (!empty($search)) {
            $countQuery .= " AND (order_number LIKE ? OR user_id IN (SELECT id FROM users WHERE username LIKE ? OR email LIKE ?))";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
        }

        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['orders' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

/**
 * Get order status stats
 */
function get_order_status_stats($pdo) {
    try {
        $stats = [];
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

        foreach ($statuses as $status) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count, SUM(total_amount) as total_amount
                FROM orders
                WHERE status = ?
            ");
            $stmt->execute([$status]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats[$status] = [
                'count' => $result['count'] ?? 0,
                'total_amount' => $result['total_amount'] ?? 0
            ];
        }

        return $stats;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Send order status notification
 */
function send_order_status_notification($pdo, $order_id, $new_status) {
    try {
        $order = get_order_by_id($pdo, $order_id);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $user_email = $order['email'];
        $user_name = $order['full_name'] ?? $order['username'];

        $status_messages = [
            'pending' => 'Your order has been received and is awaiting confirmation.',
            'confirmed' => 'Your order has been confirmed and will be processed soon.',
            'processing' => 'Your order is being prepared for shipment.',
            'shipped' => 'Your order has been shipped. Track your package with the tracking number provided.',
            'delivered' => 'Your order has been delivered successfully.',
            'cancelled' => 'Your order has been cancelled.',
            'refunded' => 'Your order refund has been processed.'
        ];

        $message = $status_messages[$new_status] ?? 'Your order status has been updated.';

        // TODO: Implement email sending
        // send_email($user_email, "Order #" . $order['order_number'] . " Status Update", $message);

        return ['success' => true, 'message' => 'Notification sent to ' . $user_email];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Export orders to CSV
 */
function export_orders_to_csv($pdo, $status = '') {
    try {
        $query = "
            SELECT o.*, u.username, u.email, u.phone
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
        ";

        if (!empty($status)) {
            $query .= " WHERE o.status = ?";
        }

        $query .= " ORDER BY o.created_at DESC";

        $stmt = $pdo->prepare($query);
        if (!empty($status)) {
            $stmt->execute([$status]);
        } else {
            $stmt->execute();
        }

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = 'orders_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = ['Order ID', 'Order Number', 'Customer', 'Email', 'Phone', 'Status', 'Total Amount', 'Created At'];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $file = fopen('php://output', 'w');
        fputcsv($file, $headers);

        foreach ($orders as $order) {
            fputcsv($file, [
                $order['id'],
                $order['order_number'],
                $order['username'],
                $order['email'],
                $order['phone'],
                $order['status'],
                $order['total_amount'],
                $order['created_at']
            ]);
        }

        fclose($file);
        exit;
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get order statistics by date range
 */
function get_order_stats_by_date($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                status,
                COUNT(*) as count,
                SUM(total_amount) as total_amount
            FROM orders
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at), status
            ORDER BY date ASC
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

?>