<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_reports_function.php

/**
 * Get sales report by date range
 */
function get_sales_report($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_order_value,
                COUNT(DISTINCT user_id) as unique_customers
            FROM orders 
            WHERE status = 'completed' AND created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get user growth report
 */
function get_user_growth_report($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_users,
                status
            FROM users 
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

/**
 * Get revenue report by payment gateway
 */
function get_revenue_by_gateway_report($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                gateway,
                COUNT(*) as transactions,
                SUM(amount) as total_revenue,
                AVG(amount) as avg_transaction,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_revenue,
                SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed_amount
            FROM payments 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY gateway
            ORDER BY total_revenue DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get top products report
 */
function get_top_products_report($pdo, $start_date, $end_date, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                oi.product_id,
                oi.product_name,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.subtotal) as total_revenue,
                COUNT(DISTINCT oi.order_id) as orders_count,
                AVG(oi.price) as avg_price
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ? AND o.status = 'completed'
            GROUP BY oi.product_id, oi.product_name
            ORDER BY total_revenue DESC
            LIMIT ?
        ");
        $stmt->execute([$start_date, $end_date, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get customer segmentation report
 */
function get_customer_segmentation_report($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                CASE 
                    WHEN total_spent = 0 THEN 'Inactive'
                    WHEN total_spent < 5000 THEN 'Low Value'
                    WHEN total_spent < 20000 THEN 'Medium Value'
                    WHEN total_spent < 50000 THEN 'High Value'
                    ELSE 'Premium'
                END as segment,
                COUNT(*) as customer_count,
                SUM(total_spent) as segment_revenue,
                AVG(total_spent) as avg_spent,
                AVG(order_count) as avg_orders
            FROM (
                SELECT 
                    u.id,
                    SUM(COALESCE(o.total_amount, 0)) as total_spent,
                    COUNT(o.id) as order_count
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'completed'
                GROUP BY u.id
            ) customer_data
            GROUP BY segment
            ORDER BY segment_revenue DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get transaction report
 */
function get_transaction_report($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_transactions,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed_amount,
                ROUND(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as success_rate
            FROM payments 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get order status report
 */
function get_order_status_report($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as order_count,
                SUM(total_amount) as total_revenue,
                ROUND(COUNT(*) / (SELECT COUNT(*) FROM orders WHERE created_at BETWEEN ? AND ?) * 100, 2) as percentage
            FROM orders 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY status
            ORDER BY order_count DESC
        ");
        $stmt->execute([$start_date, $end_date, $start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get user activity report
 */
function get_user_activity_report($pdo, $days = 30) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(last_login) as date,
                COUNT(*) as active_users,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as verified_users
            FROM users 
            WHERE last_login IS NOT NULL AND last_login >= DATE_SUB(NOW(), INTERVAL ? DAY)
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
 * Get financial summary report
 */
function get_financial_summary($pdo, $start_date, $end_date) {
    try {
        $summary = [];

        // Total revenue
        $stmt = $pdo->prepare("
            SELECT SUM(total_amount) as total 
            FROM orders 
            WHERE status = 'completed' AND created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        $summary['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Total orders
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM orders 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        $summary['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total transactions
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count, SUM(amount) as total 
            FROM payments 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $summary['total_transactions'] = $result['count'] ?? 0;
        $summary['transaction_amount'] = $result['total'] ?? 0;

        // Refunded amount
        $stmt = $pdo->prepare("
            SELECT SUM(total_amount) as total 
            FROM orders 
            WHERE status = 'refunded' AND created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        $summary['refunded_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // New customers
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        $summary['new_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Average order value
        $summary['avg_order_value'] = $summary['total_orders'] > 0 ? $summary['total_revenue'] / $summary['total_orders'] : 0;

        return $summary;
    } catch (Exception $e) {
        return [
            'total_revenue' => 0,
            'total_orders' => 0,
            'total_transactions' => 0,
            'transaction_amount' => 0,
            'refunded_amount' => 0,
            'new_customers' => 0,
            'avg_order_value' => 0
        ];
    }
}

/**
 * Get coupon/discount report
 */
function get_discount_report($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_discounts,
                SUM(discount_amount) as total_discount_value,
                AVG(discount_amount) as avg_discount,
                COUNT(DISTINCT order_id) as orders_with_discount
            FROM order_discounts 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'total_discounts' => 0,
            'total_discount_value' => 0,
            'avg_discount' => 0,
            'orders_with_discount' => 0
        ];
    }
}

/**
 * Export report to CSV
 */
function export_report_to_csv($filename, $data, $headers) {
    try {
        $file = fopen('php://output', 'w');
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Write column headers
        fputcsv($file, $headers);
        
        // Write data rows
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
        exit;
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

?>