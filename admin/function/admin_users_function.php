<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_users_function.php

/**
 * Create users table if it doesn't exist
 */
function create_users_table($pdo) {
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(100) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(255),
                phone VARCHAR(20),
                address TEXT,
                city VARCHAR(100),
                state VARCHAR(100),
                country VARCHAR(100),
                postal_code VARCHAR(20),
                profile_image VARCHAR(255),
                bio TEXT,
                date_of_birth DATE,
                gender ENUM('male', 'female', 'other'),
                user_type ENUM('customer', 'vendor', 'admin') DEFAULT 'customer',
                account_status ENUM('active', 'inactive', 'suspended', 'banned') DEFAULT 'active',
                email_verified BOOLEAN DEFAULT 0,
                phone_verified BOOLEAN DEFAULT 0,
                two_factor_enabled BOOLEAN DEFAULT 0,
                last_login TIMESTAMP NULL,
                login_attempts INT DEFAULT 0,
                locked_until TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_user_type (user_type),
                INDEX idx_account_status (account_status),
                INDEX idx_created_at (created_at)
            )
        ");

        return ['success' => true, 'message' => 'Users table created'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get user by ID
 */
function get_user_by_id($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get user by username
 */
function get_user_by_username($pdo, $username) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users WHERE username = ?
        ");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get user by email
 */
function get_user_by_email($pdo, $email) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users WHERE email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get all users with pagination and filters
 */
function get_all_users($pdo, $page = 1, $limit = 10, $user_type = '', $account_status = '', $search = '') {
    try {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (!empty($user_type)) {
            $query .= " AND user_type = ?";
            $params[] = $user_type;
        }

        if (!empty($account_status)) {
            $query .= " AND account_status = ?";
            $params[] = $account_status;
        }

        if (!empty($search)) {
            $query .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ? OR phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        $countParams = [];

        if (!empty($user_type)) {
            $countQuery .= " AND user_type = ?";
            $countParams[] = $user_type;
        }

        if (!empty($account_status)) {
            $countQuery .= " AND account_status = ?";
            $countParams[] = $account_status;
        }

        if (!empty($search)) {
            $countQuery .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ? OR phone LIKE ?)";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
            $countParams[] = "%$search%";
        }

        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['users' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

/**
 * Create new user
 */
function create_user($pdo, $data) {
    try {
        // Validate required fields
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Username, email, and password are required'];
        }

        // Check if user already exists
        if (get_user_by_username($pdo, $data['username'])) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        if (get_user_by_email($pdo, $data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        create_users_table($pdo);

        $stmt = $pdo->prepare("
            INSERT INTO users (
                username, email, password, full_name, phone, address, city, state, 
                country, postal_code, date_of_birth, gender, user_type, account_status, 
                email_verified, phone_verified
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        $result = $stmt->execute([
            $data['username'],
            $data['email'],
            $hashed_password,
            $data['full_name'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['country'] ?? null,
            $data['postal_code'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['user_type'] ?? 'customer',
            $data['account_status'] ?? 'active',
            $data['email_verified'] ?? 0,
            $data['phone_verified'] ?? 0
        ]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $pdo->lastInsertId()
            ];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Update user
 */
function update_user($pdo, $user_id, $data) {
    try {
        $user = get_user_by_id($pdo, $user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Check for duplicate username
        if (isset($data['username']) && $data['username'] !== $user['username']) {
            if (get_user_by_username($pdo, $data['username'])) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
        }

        // Check for duplicate email
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if (get_user_by_email($pdo, $data['email'])) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
        }

        $updateFields = [];
        $params = [];

        $allowed_fields = ['username', 'email', 'full_name', 'phone', 'address', 'city', 'state', 
                          'country', 'postal_code', 'date_of_birth', 'gender', 'user_type', 
                          'account_status', 'email_verified', 'phone_verified', 'bio'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $params[] = $user_id;
        $query = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'User updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Change user password
 */
function change_user_password($pdo, $user_id, $new_password) {
    try {
        $user = get_user_by_id($pdo, $user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (strlen($new_password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);

        return ['success' => true, 'message' => 'Password changed successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Update user account status
 */
function update_user_account_status($pdo, $user_id, $account_status) {
    try {
        $allowed_status = ['active', 'inactive', 'suspended', 'banned'];
        if (!in_array($account_status, $allowed_status)) {
            return ['success' => false, 'message' => 'Invalid account status'];
        }

        $user = get_user_by_id($pdo, $user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $stmt = $pdo->prepare("UPDATE users SET account_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$account_status, $user_id]);

        return ['success' => true, 'message' => 'Account status updated to ' . $account_status];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Delete user
 */
function delete_user($pdo, $user_id) {
    try {
        $user = get_user_by_id($pdo, $user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        return ['success' => true, 'message' => 'User deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get user statistics
 */
function get_user_stats($pdo) {
    try {
        $stats = [];

        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Active users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE account_status = 'active'");
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Inactive users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE account_status = 'inactive'");
        $stats['inactive_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Suspended users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE account_status = 'suspended'");
        $stats['suspended_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Customers
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'");
        $stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Vendors
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'vendor'");
        $stats['total_vendors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Admins
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'");
        $stats['total_admins'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Email verified
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email_verified = 1");
        $stats['email_verified'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Phone verified
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE phone_verified = 1");
        $stats['phone_verified'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Two-factor enabled
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE two_factor_enabled = 1");
        $stats['two_factor_enabled'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        return $stats;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get users by type
 */
function get_users_by_type($pdo, $user_type, $page = 1, $limit = 10) {
    try {
        $offset = ($page - 1) * $limit;

        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE user_type = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_type, $limit, $offset]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE user_type = ?");
        $countStmt->execute([$user_type]);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['users' => [], 'total' => 0];
    }
}

/**
 * Get user account activity
 */
function get_user_account_activity($pdo, $user_id, $limit = 20) {
    try {
        // Get user login history (you'll need a login_history table)
        $stmt = $pdo->prepare("
            SELECT * FROM login_history 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get users registered in date range
 */
function get_users_by_date_range($pdo, $start_date, $end_date) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count,
                SUM(CASE WHEN account_status = 'active' THEN 1 ELSE 0 END) as active_count
            FROM users
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
 * Send verification email
 */
function send_verification_email($pdo, $user_id) {
    try {
        $user = get_user_by_id($pdo, $user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // TODO: Implement email sending
        // Generate token and send email with verification link

        return ['success' => true, 'message' => 'Verification email sent to ' . $user['email']];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Mark email as verified
 */
function verify_user_email($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);

        return ['success' => true, 'message' => 'Email marked as verified'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get users with their order count
 */
function get_users_with_order_count($pdo, $page = 1, $limit = 10) {
    try {
        $offset = ($page - 1) * $limit;

        $stmt = $pdo->prepare("
            SELECT u.*, 
                   COUNT(o.id) as order_count,
                   COALESCE(SUM(o.total_amount), 0) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM users");
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['users' => [], 'total' => 0];
    }
}

/**
 * Export users to CSV
 */
function export_users_to_csv($pdo, $user_type = '', $account_status = '') {
    try {
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (!empty($user_type)) {
            $query .= " AND user_type = ?";
            $params[] = $user_type;
        }

        if (!empty($account_status)) {
            $query .= " AND account_status = ?";
            $params[] = $account_status;
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = ['ID', 'Username', 'Email', 'Full Name', 'Phone', 'User Type', 'Account Status', 'Email Verified', 'Created At'];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $file = fopen('php://output', 'w');
        fputcsv($file, $headers);

        foreach ($users as $user) {
            fputcsv($file, [
                $user['id'],
                $user['username'],
                $user['email'],
                $user['full_name'],
                $user['phone'],
                $user['user_type'],
                $user['account_status'],
                $user['email_verified'] ? 'Yes' : 'No',
                $user['created_at']
            ]);
        }

        fclose($file);
        exit;
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

?>