<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_dashboard_function.php

/**
 * Get admin dashboard statistics
 */
function get_admin_stats($pdo) {
    try {
        $stats = [];

        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Active users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Inactive users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'");
        $stats['inactive_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Suspended users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'suspended'");
        $stats['suspended_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total balance in system
        $stmt = $pdo->query("SELECT SUM(balance) as total FROM wallets");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_balance'] = $result['total'] ?? 0;

        // Total transactions
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
        $stats['total_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total revenue
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $result['total'] ?? 0;

        // Total orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Pending orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total tickets
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM tickets");
        $stats['total_tickets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Open tickets
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM tickets WHERE status = 'open'");
        $stats['open_tickets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        return $stats;
    } catch (Exception $e) {
        return [
            'total_users' => 0,
            'active_users' => 0,
            'inactive_users' => 0,
            'suspended_users' => 0,
            'total_balance' => 0,
            'total_transactions' => 0,
            'total_revenue' => 0,
            'total_orders' => 0,
            'pending_orders' => 0,
            'total_tickets' => 0,
            'open_tickets' => 0,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get recent transactions
 */
function get_recent_transactions($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, u.email 
            FROM payments p 
            LEFT JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get recent users
 */
function get_recent_users($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, full_name, status, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get recent orders
 */
function get_recent_orders($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get revenue by date (last 7 days)
 */
function get_revenue_chart($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as date, SUM(amount) as revenue, COUNT(*) as transactions
            FROM payments 
            WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get user registration trend (last 30 days)
 */
function get_user_registration_chart($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get payment gateway breakdown
 */
function get_payment_gateway_breakdown($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT gateway, COUNT(*) as count, SUM(amount) as total
            FROM payments 
            WHERE status = 'completed'
            GROUP BY gateway
            ORDER BY total DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get user status breakdown
 */
function get_user_status_breakdown($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count
            FROM users 
            GROUP BY status
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get order status breakdown
 */
function get_order_status_breakdown($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count
            FROM orders 
            GROUP BY status
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get ticket status breakdown
 */
function get_ticket_status_breakdown($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count
            FROM tickets 
            GROUP BY status
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get top users by spending
 */
function get_top_spenders($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.email, SUM(p.amount) as total_spent, COUNT(p.id) as transaction_count
            FROM users u 
            LEFT JOIN payments p ON u.id = p.user_id AND p.status = 'completed'
            GROUP BY u.id
            ORDER BY total_spent DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get system health status
 */
function get_system_health($pdo) {
    try {
        $health = [
            'database' => true,
            'last_sync' => date('Y-m-d H:i:s')
        ];

        // Test database connection
        $pdo->query("SELECT 1");

        return $health;
    } catch (Exception $e) {
        return [
            'database' => false,
            'error' => $e->getMessage(),
            'last_sync' => date('Y-m-d H:i:s')
        ];
    }
}

/**
 * Get daily active users
 */
function get_daily_active_users($pdo, $days = 7) {
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(last_login) as date, COUNT(DISTINCT id) as active_users
            FROM users 
            WHERE last_login >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(last_login)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get all dashboard data at once
 */
function get_admin_dashboard_data($pdo) {
    try {
        return [
            'stats' => get_admin_stats($pdo),
            'recent_transactions' => get_recent_transactions($pdo, 5),
            'recent_users' => get_recent_users($pdo, 5),
            'recent_orders' => get_recent_orders($pdo, 5),
            'revenue_chart' => get_revenue_chart($pdo),
            'user_registration' => get_user_registration_chart($pdo),
            'payment_gateways' => get_payment_gateway_breakdown($pdo),
            'user_status' => get_user_status_breakdown($pdo),
            'order_status' => get_order_status_breakdown($pdo),
            'ticket_status' => get_ticket_status_breakdown($pdo),
            'top_spenders' => get_top_spenders($pdo, 5),
            'system_health' => get_system_health($pdo),
            'daily_active_users' => get_daily_active_users($pdo, 7)
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

?>