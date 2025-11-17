<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_orders_function.php

/**
 * Create orders table if it doesn't exist
 */
function create_orders_table($pdo) {
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS orders (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                order_number VARCHAR(50) UNIQUE,
                total_amount DECIMAL(10, 2) NOT NULL,
                status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
                payment_method VARCHAR(50),
                shipping_address LONGTEXT,
                notes LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // Create order items table
        $pdo->query("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                product_id INT,
                product_name VARCHAR(255),
                quantity INT DEFAULT 1,
                price DECIMAL(10, 2),
                subtotal DECIMAL(10, 2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            )
        ");

        return ['success' => true, 'message' => 'Orders tables created successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Create new order
 */
function create_order($pdo, $data) {
    try {
        // Validate required fields
        if (empty($data['user_id']) || empty($data['total_amount']) || empty($data['items'])) {
            return ['success' => false, 'message' => 'User ID, total amount, and items are required'];
        }

        create_orders_table($pdo);

        // Generate order number
        $order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, shipping_address, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['user_id'],
            $order_number,
            (float)$data['total_amount'],
            $data['status'] ?? 'pending',
            $data['payment_method'] ?? null,
            $data['shipping_address'] ?? null,
            $data['notes'] ?? null
        ]);

        if ($result) {
            $order_id = $pdo->lastInsertId();

            // Insert order items
            $item_stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($data['items'] as $item) {
                $item_stmt->execute([
                    $order_id,
                    $item['product_id'] ?? null,
                    $item['product_name'],
                    $item['quantity'],
                    (float)$item['price'],
                    (float)($item['quantity'] * $item['price'])
                ]);
            }

            return [
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order_id,
                'order_number' => $order_number
            ];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get order by ID with items
 */
function get_order_by_id($pdo, $order_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email, u.full_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Get order items
            $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $items_stmt->execute([$order_id]);
            $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $order;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get all orders with pagination
 */
function get_all_orders($pdo, $page = 1, $limit = 10, $status = '', $user_id = '', $search = '') {
    try {
        $offset = ($page - 1) * $limit;
        $query = "SELECT o.*, u.username, u.email, COUNT(oi.id) as item_count 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  WHERE 1=1";
        $params = [];

        if (!empty($status)) {
            $query .= " AND o.status = ?";
            $params[] = $status;
        }

        if (!empty($user_id)) {
            $query .= " AND o.user_id = ?";
            $params[] = $user_id;
        }

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

        // Get total count
        $countQuery = "SELECT COUNT(DISTINCT o.id) as total FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       WHERE 1=1";
        $countParams = [];

        if (!empty($status)) {
            $countQuery .= " AND o.status = ?";
            $countParams[] = $status;
        }

        if (!empty($user_id)) {
            $countQuery .= " AND o.user_id = ?";
            $countParams[] = $user_id;
        }

        if (!empty($search)) {
            $countQuery .= " AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
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
 * Update order status
 */
function update_order_status($pdo, $order_id, $status) {
    try {
        $allowed_status = ['pending', 'processing', 'completed', 'cancelled', 'refunded'];
        if (!in_array($status, $allowed_status)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        return ['success' => true, 'message' => 'Order status updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Update order
 */
function update_order($pdo, $order_id, $data) {
    try {
        $updates = [];
        $params = [];

        if (isset($data['status'])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }
        if (isset($data['payment_method'])) {
            $updates[] = "payment_method = ?";
            $params[] = $data['payment_method'];
        }
        if (isset($data['shipping_address'])) {
            $updates[] = "shipping_address = ?";
            $params[] = $data['shipping_address'];
        }
        if (isset($data['notes'])) {
            $updates[] = "notes = ?";
            $params[] = $data['notes'];
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $params[] = $order_id;
        $query = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Order updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Delete order
 */
function delete_order($pdo, $order_id) {
    try {
        // Delete order items first
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);

        // Delete order
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);

        return ['success' => true, 'message' => 'Order deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get order statistics
 */
function get_orders_stats($pdo) {
    try {
        $stats = [];

        // Total orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Pending orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Processing orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'");
        $stats['processing_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Completed orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'");
        $stats['completed_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total revenue
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('completed', 'processing')");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $result['total'] ?? 0;

        // Average order value
        $stmt = $pdo->query("SELECT AVG(total_amount) as average FROM orders WHERE status = 'completed'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['average_order_value'] = $result['average'] ?? 0;

        return $stats;
    } catch (Exception $e) {
        return [
            'total_orders' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'completed_orders' => 0,
            'total_revenue' => 0,
            'average_order_value' => 0
        ];
    }
}

/**
 * Get orders by date range
 */
function get_orders_by_date_range($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get revenue chart data
 */
function get_orders_revenue_chart($pdo, $days = 30) {
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
            FROM orders 
            WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

?>