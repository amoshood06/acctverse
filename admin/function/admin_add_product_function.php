<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_add_product_function.php

/**
 * Create products table if it doesn't exist
 */
function create_products_table($pdo) {
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS products (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                description LONGTEXT,
                price DECIMAL(10, 2) NOT NULL,
                stock INT DEFAULT 0,
                category VARCHAR(100),
                image_url VARCHAR(255),
                sku VARCHAR(100) UNIQUE,
                status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )
        ");
        return ['success' => true, 'message' => 'Products table created successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Add new product
 */
function add_product($pdo, $data) {
    try {
        // Validate required fields
        if (empty($data['name']) || empty($data['price'])) {
            return ['success' => false, 'message' => 'Product name and price are required'];
        }

        // Create products table if not exists
        create_products_table($pdo);

        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, stock, category, image_url, sku, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            (float)$data['price'],
            (int)($data['stock'] ?? 0),
            $data['category'] ?? null,
            $data['image_url'] ?? null,
            $data['sku'] ?? null,
            $data['status'] ?? 'active',
            $data['created_by'] ?? null
        ]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Product added successfully',
                'product_id' => $pdo->lastInsertId()
            ];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Update product
 */
function update_product($pdo, $product_id, $data) {
    try {
        if (empty($product_id)) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }

        $updates = [];
        $params = [];

        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
        }
        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
        }
        if (isset($data['price'])) {
            $updates[] = "price = ?";
            $params[] = (float)$data['price'];
        }
        if (isset($data['stock'])) {
            $updates[] = "stock = ?";
            $params[] = (int)$data['stock'];
        }
        if (isset($data['category'])) {
            $updates[] = "category = ?";
            $params[] = $data['category'];
        }
        if (isset($data['image_url'])) {
            $updates[] = "image_url = ?";
            $params[] = $data['image_url'];
        }
        if (isset($data['sku'])) {
            $updates[] = "sku = ?";
            $params[] = $data['sku'];
        }
        if (isset($data['status'])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $params[] = $product_id;
        $query = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Product updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get product by ID
 */
function get_product_by_id($pdo, $product_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get all products with pagination
 */
function get_all_products($pdo, $page = 1, $limit = 10, $category = '', $status = '', $search = '') {
    try {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if (!empty($category)) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if (!empty($status)) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $query .= " AND (name LIKE ? OR sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM products WHERE 1=1";
        $countParams = [];

        if (!empty($category)) {
            $countQuery .= " AND category = ?";
            $countParams[] = $category;
        }

        if (!empty($status)) {
            $countQuery .= " AND status = ?";
            $countParams[] = $status;
        }

        if (!empty($search)) {
            $countQuery .= " AND (name LIKE ? OR sku LIKE ?)";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
        }

        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['products' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

/**
 * Delete product
 */
function delete_product($pdo, $product_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        return ['success' => true, 'message' => 'Product deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get product categories
 */
function get_product_categories($pdo) {
    try {
        $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get product statistics
 */
function get_product_stats($pdo) {
    try {
        $stats = [];

        // Total products
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Active products
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
        $stats['active_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Out of stock
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock = 0");
        $stats['out_of_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Low stock (less than 10)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock > 0 AND stock < 10");
        $stats['low_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total inventory value
        $stmt = $pdo->query("SELECT SUM(price * stock) as total FROM products");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['inventory_value'] = $result['total'] ?? 0;

        return $stats;
    } catch (Exception $e) {
        return [
            'total_products' => 0,
            'active_products' => 0,
            'out_of_stock' => 0,
            'low_stock' => 0,
            'inventory_value' => 0
        ];
    }
}

/**
 * Upload product image
 */
function upload_product_image($file) {
    try {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No file uploaded or upload error'];
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP allowed'];
        }

        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = time() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'message' => 'Image uploaded successfully',
                'filename' => $filename,
                'filepath' => $filepath
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to move uploaded file'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

?>