<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_dashboard_functions.php

/**
 * Get total users count
 */
function get_total_users($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get users growth percentage
 */
function get_users_growth($pdo) {
    try {
        $current_month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));

        $current = $pdo->query("
            SELECT COUNT(*) as count FROM users 
            WHERE status = 'active' AND DATE_FORMAT(created_at, '%Y-%m') = '$current_month'
        ");
        $current_count = (int)($current->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        $last = $pdo->query("
            SELECT COUNT(*) as count FROM users 
            WHERE status = 'active' AND DATE_FORMAT(created_at, '%Y-%m') = '$last_month'
        ");
        $last_count = (int)($last->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        if ($last_count == 0) {
            return ['growth' => 0, 'positive' => false];
        }

        $growth = (($current_count - $last_count) / $last_count) * 100;
        return ['growth' => round($growth, 1), 'positive' => $growth >= 0];
    } catch (Exception $e) {
        return ['growth' => 0, 'positive' => false];
    }
}

/**
 * Get total transactions count
 */
function get_total_transactions($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments WHERE status IN ('success', 'pending')");
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get transactions growth percentage
 */
function get_transactions_growth($pdo) {
    try {
        $current_month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));

        $current = $pdo->query("
            SELECT COUNT(*) as count FROM payments 
            WHERE status IN ('success', 'pending') AND DATE_FORMAT(created_at, '%Y-%m') = '$current_month'
        ");
        $current_count = (int)($current->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        $last = $pdo->query("
            SELECT COUNT(*) as count FROM payments 
            WHERE status IN ('success', 'pending') AND DATE_FORMAT(created_at, '%Y-%m') = '$last_month'
        ");
        $last_count = (int)($last->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        if ($last_count == 0) {
            return ['growth' => 0, 'positive' => false];
        }

        $growth = (($current_count - $last_count) / $last_count) * 100;
        return ['growth' => round($growth, 1), 'positive' => $growth >= 0];
    } catch (Exception $e) {
        return ['growth' => 0, 'positive' => false];
    }
}

/**
 * Get total revenue
 */
function get_total_revenue($pdo) {
    try {
        $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success'");
        return (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get revenue growth percentage
 */
function get_revenue_growth($pdo) {
    try {
        $current_month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));

        $current = $pdo->query("
            SELECT COALESCE(SUM(amount), 0) as total FROM payments 
            WHERE status = 'success' AND DATE_FORMAT(created_at, '%Y-%m') = '$current_month'
        ");
        $current_revenue = (float)($current->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $last = $pdo->query("
            SELECT COALESCE(SUM(amount), 0) as total FROM payments 
            WHERE status = 'success' AND DATE_FORMAT(created_at, '%Y-%m') = '$last_month'
        ");
        $last_revenue = (float)($last->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        if ($last_revenue == 0) {
            return ['growth' => 0, 'positive' => false];
        }

        $growth = (($current_revenue - $last_revenue) / $last_revenue) * 100;
        return ['growth' => round($growth, 1), 'positive' => $growth >= 0];
    } catch (Exception $e) {
        return ['growth' => 0, 'positive' => false];
    }
}

/**
 * Get pending orders count
 */
function get_pending_orders($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'processing')");
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get recent user activity
 */
function get_recent_user_activity($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.created_at,
                CASE 
                    WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'Registered new account'
                    ELSE 'Active user'
                END as activity,
                u.created_at as last_activity
            FROM users u
            WHERE u.status = 'active'
            ORDER BY u.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get system status
 */
function get_system_status($pdo) {
    try {
        $status = [
            'server' => 'Operational',
            'database' => 'Healthy',
            'api' => 'Active',
            'payment_gateway' => 'Connected',
            'sms_service' => 'Limited'
        ];

        // Check database connection
        try {
            $pdo->query("SELECT 1");
            $status['database'] = 'Healthy';
        } catch (Exception $e) {
            $status['database'] = 'Error';
        }

        return $status;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get recent transactions
 */
function get_recent_transactions($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                u.name as user_name,
                u.email,
                p.amount,
                p.gateway as type,
                p.status,
                p.created_at
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.status IN ('success', 'pending', 'failed')
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
 * Get dashboard summary - MAIN FUNCTION
 */
function get_dashboard_summary($pdo) {
    try {
        return [
            'total_users' => get_total_users($pdo),
            'users_growth' => get_users_growth($pdo),
            'total_transactions' => get_total_transactions($pdo),
            'transactions_growth' => get_transactions_growth($pdo),
            'total_revenue' => get_total_revenue($pdo),
            'revenue_growth' => get_revenue_growth($pdo),
            'pending_orders' => get_pending_orders($pdo),
            'recent_activity' => get_recent_user_activity($pdo, 5),
            'system_status' => get_system_status($pdo),
            'recent_transactions' => get_recent_transactions($pdo, 10)
        ];
    } catch (Exception $e) {
        return [
            'total_users' => 0,
            'users_growth' => ['growth' => 0, 'positive' => false],
            'total_transactions' => 0,
            'transactions_growth' => ['growth' => 0, 'positive' => false],
            'total_revenue' => 0,
            'revenue_growth' => ['growth' => 0, 'positive' => false],
            'pending_orders' => 0,
            'recent_activity' => [],
            'system_status' => [],
            'recent_transactions' => []
        ];
    }
}

/**
 * Get revenue by gateway
 */
function get_revenue_by_gateway($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                gateway,
                COUNT(*) as transaction_count,
                COALESCE(SUM(amount), 0) as total_amount
            FROM payments
            WHERE status = 'success'
            GROUP BY gateway
            ORDER BY total_amount DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get daily revenue chart data
 */
function get_daily_revenue_chart($pdo, $days = 7) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COALESCE(SUM(amount), 0) as revenue,
                COUNT(*) as transaction_count
            FROM payments
            WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure we have data for all days
        if (empty($data)) {
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $data[] = [
                    'date' => $date,
                    'revenue' => 0,
                    'transaction_count' => 0
                ];
            }
        }
        
        return $data;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get user registration chart data
 */
function get_user_registration_chart($pdo, $days = 7) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_users
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure we have data for all days
        if (empty($data)) {
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $data[] = [
                    'date' => $date,
                    'new_users' => 0
                ];
            }
        }
        
        return $data;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get order status summary
 */
function get_order_status_summary($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                status,
                COUNT(*) as count,
                COALESCE(SUM(total_amount), 0) as total_value
            FROM orders
            GROUP BY status
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get top performing products
 */
function get_top_products($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.name,
                COUNT(oi.id) as order_count,
                COALESCE(SUM(oi.total_price), 0) as total_revenue,
                p.price,
                p.icon
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            GROUP BY p.id, p.name, p.price, p.icon
            ORDER BY total_revenue DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    } catch (Exception $e) {
        return [];
    }
}

?>