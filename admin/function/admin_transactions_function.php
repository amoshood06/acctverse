<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_transactions_function.php

/**
 * Create payments/transactions table if it doesn't exist
 */
function create_transactions_table($pdo) {
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS payments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                order_id INT,
                transaction_id VARCHAR(100) UNIQUE,
                amount DECIMAL(10, 2) NOT NULL,
                currency VARCHAR(3) DEFAULT 'NGN',
                gateway VARCHAR(50),
                status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
                payment_method VARCHAR(100),
                reference_number VARCHAR(100),
                description TEXT,
                metadata LONGTEXT,
                payer_email VARCHAR(255),
                payer_name VARCHAR(255),
                receipt_url VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
                INDEX idx_user_id (user_id),
                INDEX idx_status (status),
                INDEX idx_gateway (gateway),
                INDEX idx_created_at (created_at)
            )
        ");

        // Create refunds table
        $pdo->query("
            CREATE TABLE IF NOT EXISTS refunds (
                id INT PRIMARY KEY AUTO_INCREMENT,
                payment_id INT NOT NULL,
                amount DECIMAL(10, 2) NOT NULL,
                reason VARCHAR(255),
                status ENUM('pending', 'approved', 'rejected', 'processed') DEFAULT 'pending',
                requested_by INT,
                approved_by INT,
                notes LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
                FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
            )
        ");

        return ['success' => true, 'message' => 'Transactions tables created successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Create a new transaction/payment record
 */
function create_transaction($pdo, $data) {
    try {
        if (empty($data['user_id']) || empty($data['amount'])) {
            return ['success' => false, 'message' => 'User ID and amount are required'];
        }

        create_transactions_table($pdo);

        $stmt = $pdo->prepare("
            INSERT INTO payments (user_id, order_id, transaction_id, amount, currency, gateway, status, payment_method, reference_number, description, payer_email, payer_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['user_id'],
            $data['order_id'] ?? null,
            $data['transaction_id'] ?? null,
            (float)$data['amount'],
            $data['currency'] ?? 'NGN',
            $data['gateway'] ?? null,
            $data['status'] ?? 'pending',
            $data['payment_method'] ?? null,
            $data['reference_number'] ?? null,
            $data['description'] ?? null,
            $data['payer_email'] ?? null,
            $data['payer_name'] ?? null
        ]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Transaction created successfully',
                'payment_id' => $pdo->lastInsertId()
            ];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get transaction by ID
 */
function get_transaction_by_id($pdo, $payment_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, u.email, u.full_name, o.order_number
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN orders o ON p.order_id = o.id
            WHERE p.id = ?
        ");
        $stmt->execute([$payment_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get all transactions with pagination and filters
 */
function get_all_transactions($pdo, $page = 1, $limit = 10, $status = '', $gateway = '', $search = '') {
    try {
        $offset = ($page - 1) * $limit;
        $query = "
            SELECT p.*, u.username, u.email, o.order_number
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN orders o ON p.order_id = o.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($status)) {
            $query .= " AND p.status = ?";
            $params[] = $status;
        }

        if (!empty($gateway)) {
            $query .= " AND p.gateway = ?";
            $params[] = $gateway;
        }

        if (!empty($search)) {
            $query .= " AND (p.transaction_id LIKE ? OR p.reference_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM payments p LEFT JOIN users u ON p.user_id = u.id WHERE 1=1";
        $countParams = [];

        if (!empty($status)) {
            $countQuery .= " AND p.status = ?";
            $countParams[] = $status;
        }

        if (!empty($gateway)) {
            $countQuery .= " AND p.gateway = ?";
            $countParams[] = $gateway;
        }

        if (!empty($search)) {
            $countQuery .= " AND (p.transaction_id LIKE ? OR p.reference_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
        }

        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['transactions' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

/**
 * Get transactions by user
 */
function get_user_transactions($pdo, $user_id, $page = 1, $limit = 10) {
    try {
        $offset = ($page - 1) * $limit;

        $stmt = $pdo->prepare("
            SELECT * FROM payments 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM payments WHERE user_id = ?");
        $countStmt->execute([$user_id]);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['transactions' => [], 'total' => 0];
    }
}

/**
 * Update transaction status
 */
function update_transaction_status($pdo, $payment_id, $status) {
    try {
        $allowed_status = ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'];
        
        if (!in_array($status, $allowed_status)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $stmt = $pdo->prepare("UPDATE payments SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $payment_id]);

        return ['success' => true, 'message' => 'Transaction status updated'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get transaction statistics
 */
function get_transaction_stats($pdo, $start_date = null, $end_date = null) {
    try {
        $stats = [];

        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }

        // Total transactions
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE created_at BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $stats['total_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total amount
        $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE created_at BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $stats['total_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Completed transactions
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM payments WHERE status = 'completed' AND created_at BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['completed_transactions'] = $result['count'] ?? 0;
        $stats['completed_amount'] = $result['total'] ?? 0;

        // Failed transactions
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM payments WHERE status = 'failed' AND created_at BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['failed_transactions'] = $result['count'] ?? 0;
        $stats['failed_amount'] = $result['total'] ?? 0;

        // Pending transactions
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM payments WHERE status = 'pending' AND created_at BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pending_transactions'] = $result['count'] ?? 0;
        $stats['pending_amount'] = $result['total'] ?? 0;

        // Refunded transactions
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM payments WHERE status = 'refunded' AND created_at BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['refunded_transactions'] = $result['count'] ?? 0;
        $stats['refunded_amount'] = $result['total'] ?? 0;

        // Average transaction value
        $stats['avg_transaction'] = $stats['total_transactions'] > 0 ? $stats['total_amount'] / $stats['total_transactions'] : 0;

        // Success rate
        $stats['success_rate'] = $stats['total_transactions'] > 0 ? round(($stats['completed_transactions'] / $stats['total_transactions']) * 100, 2) : 0;

        return $stats;
    } catch (Exception $e) {
        return [
            'total_transactions' => 0,
            'total_amount' => 0,
            'completed_transactions' => 0,
            'completed_amount' => 0,
            'failed_transactions' => 0,
            'failed_amount' => 0,
            'pending_transactions' => 0,
            'pending_amount' => 0,
            'refunded_transactions' => 0,
            'refunded_amount' => 0,
            'avg_transaction' => 0,
            'success_rate' => 0
        ];
    }
}

/**
 * Get transactions by gateway
 */
function get_transactions_by_gateway($pdo, $start_date = null, $end_date = null) {
    try {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }

        $stmt = $pdo->prepare("
            SELECT 
                gateway,
                COUNT(*) as transactions,
                SUM(amount) as total_amount,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                AVG(amount) as avg_amount
            FROM payments
            WHERE created_at BETWEEN ? AND ?
            GROUP BY gateway
            ORDER BY total_amount DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Create refund request
 */
function create_refund($pdo, $payment_id, $amount, $reason, $user_id = null) {
    try {
        // Verify payment exists
        $payment = get_transaction_by_id($pdo, $payment_id);
        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        if ($amount > $payment['amount']) {
            return ['success' => false, 'message' => 'Refund amount exceeds payment amount'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO refunds (payment_id, amount, reason, requested_by)
            VALUES (?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $payment_id,
            (float)$amount,
            $reason,
            $user_id
        ]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Refund request created',
                'refund_id' => $pdo->lastInsertId()
            ];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get refunds
 */
function get_refunds($pdo, $page = 1, $limit = 10, $status = '') {
    try {
        $offset = ($page - 1) * $limit;
        $query = "
            SELECT r.*, p.transaction_id, p.amount as payment_amount, u.username, u.email
            FROM refunds r
            JOIN payments p ON r.payment_id = p.id
            LEFT JOIN users u ON r.requested_by = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($status)) {
            $query .= " AND r.status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total
        $countQuery = "SELECT COUNT(*) as total FROM refunds WHERE 1=1";
        $countParams = [];
        if (!empty($status)) {
            $countQuery .= " AND status = ?";
            $countParams[] = $status;
        }

        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'refunds' => $refunds,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['refunds' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

/**
 * Approve refund
 */
function approve_refund($pdo, $refund_id, $admin_id, $notes = '') {
    try {
        $stmt = $pdo->prepare("
            UPDATE refunds 
            SET status = 'approved', approved_by = ?, notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$admin_id, $notes, $refund_id]);

        return ['success' => true, 'message' => 'Refund approved'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Reject refund
 */
function reject_refund($pdo, $refund_id, $admin_id, $notes = '') {
    try {
        $stmt = $pdo->prepare("
            UPDATE refunds 
            SET status = 'rejected', approved_by = ?, notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$admin_id, $notes, $refund_id]);

        return ['success' => true, 'message' => 'Refund rejected'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

?>