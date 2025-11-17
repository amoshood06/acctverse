<?php
// filepath: c:\xampp\htdocs\acctverse\admin\function\admin_settings_function.php

/**
 * Get all system settings
 */
function get_all_settings($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM system_settings ORDER BY setting_key ASC");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        return $result;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Create system settings table if it doesn't exist
 */
function create_settings_table($pdo) {
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(255) UNIQUE NOT NULL,
                setting_value LONGTEXT,
                setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
                description TEXT,
                is_editable BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        return ['success' => true, 'message' => 'Settings table created'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get setting by key
 */
function get_setting($pdo, $key, $default = null) {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update or create setting
 */
function update_setting($pdo, $key, $value, $type = 'string', $description = '') {
    try {
        create_settings_table($pdo);
        
        // Check if setting exists
        $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, setting_type = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->execute([$value, $type, $key]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$key, $value, $type, $description]);
        }

        return ['success' => true, 'message' => 'Setting updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get payment gateway settings
 */
function get_payment_gateways($pdo) {
    try {
        $gateways = [];
        
        $paystack_key = get_setting($pdo, 'paystack_public_key');
        if ($paystack_key) {
            $gateways['paystack'] = [
                'name' => 'Paystack',
                'enabled' => get_setting($pdo, 'paystack_enabled', false),
                'public_key' => $paystack_key,
                'secret_key' => get_setting($pdo, 'paystack_secret_key', '')
            ];
        }

        $flutterwave_key = get_setting($pdo, 'flutterwave_public_key');
        if ($flutterwave_key) {
            $gateways['flutterwave'] = [
                'name' => 'Flutterwave',
                'enabled' => get_setting($pdo, 'flutterwave_enabled', false),
                'public_key' => $flutterwave_key,
                'secret_key' => get_setting($pdo, 'flutterwave_secret_key', '')
            ];
        }

        return $gateways;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update payment gateway settings
 */
function update_payment_gateway($pdo, $gateway, $settings) {
    try {
        $gateway = strtolower($gateway);
        $allowed = ['paystack', 'flutterwave'];
        
        if (!in_array($gateway, $allowed)) {
            return ['success' => false, 'message' => 'Invalid gateway'];
        }

        foreach ($settings as $key => $value) {
            update_setting($pdo, $gateway . '_' . $key, $value);
        }

        return ['success' => true, 'message' => ucfirst($gateway) . ' settings updated'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get email settings
 */
function get_email_settings($pdo) {
    try {
        return [
            'smtp_host' => get_setting($pdo, 'smtp_host', ''),
            'smtp_port' => get_setting($pdo, 'smtp_port', '587'),
            'smtp_username' => get_setting($pdo, 'smtp_username', ''),
            'smtp_password' => get_setting($pdo, 'smtp_password', ''),
            'from_email' => get_setting($pdo, 'from_email', ''),
            'from_name' => get_setting($pdo, 'from_name', ''),
            'smtp_secure' => get_setting($pdo, 'smtp_secure', 'tls')
        ];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update email settings
 */
function update_email_settings($pdo, $settings) {
    try {
        $keys = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_email', 'from_name', 'smtp_secure'];
        
        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                update_setting($pdo, $key, $settings[$key]);
            }
        }

        return ['success' => true, 'message' => 'Email settings updated'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get SMS settings
 */
function get_sms_settings($pdo) {
    try {
        return [
            'sms_provider' => get_setting($pdo, 'sms_provider', 'twilio'),
            'sms_api_key' => get_setting($pdo, 'sms_api_key', ''),
            'sms_api_secret' => get_setting($pdo, 'sms_api_secret', ''),
            'sms_from' => get_setting($pdo, 'sms_from', ''),
            'sms_enabled' => get_setting($pdo, 'sms_enabled', false)
        ];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update SMS settings
 */
function update_sms_settings($pdo, $settings) {
    try {
        $keys = ['sms_provider', 'sms_api_key', 'sms_api_secret', 'sms_from', 'sms_enabled'];
        
        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                update_setting($pdo, $key, $settings[$key]);
            }
        }

        return ['success' => true, 'message' => 'SMS settings updated'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get general settings
 */
function get_general_settings($pdo) {
    try {
        return [
            'app_name' => get_setting($pdo, 'app_name', 'AcctGlobe'),
            'app_logo' => get_setting($pdo, 'app_logo', 'acctverse.png'),
            'company_email' => get_setting($pdo, 'company_email', ''),
            'company_phone' => get_setting($pdo, 'company_phone', ''),
            'company_address' => get_setting($pdo, 'company_address', ''),
            'currency' => get_setting($pdo, 'currency', 'NGN'),
            'timezone' => get_setting($pdo, 'timezone', 'UTC'),
            'maintenance_mode' => get_setting($pdo, 'maintenance_mode', false),
            'allow_registration' => get_setting($pdo, 'allow_registration', true)
        ];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update general settings
 */
function update_general_settings($pdo, $settings) {
    try {
        $keys = ['app_name', 'app_logo', 'company_email', 'company_phone', 'company_address', 'currency', 'timezone', 'maintenance_mode', 'allow_registration'];
        
        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                update_setting($pdo, $key, $settings[$key]);
            }
        }

        return ['success' => true, 'message' => 'General settings updated'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get security settings
 */
function get_security_settings($pdo) {
    try {
        return [
            'password_min_length' => get_setting($pdo, 'password_min_length', '8'),
            'password_require_uppercase' => get_setting($pdo, 'password_require_uppercase', true),
            'password_require_numbers' => get_setting($pdo, 'password_require_numbers', true),
            'password_require_special' => get_setting($pdo, 'password_require_special', true),
            'session_timeout' => get_setting($pdo, 'session_timeout', '30'),
            'enable_two_factor' => get_setting($pdo, 'enable_two_factor', false),
            'enable_ip_whitelist' => get_setting($pdo, 'enable_ip_whitelist', false),
            'max_login_attempts' => get_setting($pdo, 'max_login_attempts', '5')
        ];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update security settings
 */
function update_security_settings($pdo, $settings) {
    try {
        $keys = ['password_min_length', 'password_require_uppercase', 'password_require_numbers', 'password_require_special', 'session_timeout', 'enable_two_factor', 'enable_ip_whitelist', 'max_login_attempts'];
        
        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                update_setting($pdo, $key, $settings[$key]);
            }
        }

        return ['success' => true, 'message' => 'Security settings updated'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get all logs
 */
function get_activity_logs($pdo, $page = 1, $limit = 20) {
    try {
        $offset = ($page - 1) * $limit;
        
        $stmt = $pdo->prepare("
            SELECT * FROM activity_logs 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM activity_logs");
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['logs' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

/**
 * Create activity log
 */
function create_activity_log($pdo, $user_id, $action, $description = '', $ip_address = '') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $action, $description, $ip_address]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Clear activity logs
 */
function clear_activity_logs($pdo, $days = 90) {
    try {
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        return ['success' => true, 'message' => 'Activity logs cleared'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get system health status
 */
function get_system_health($pdo) {
    try {
        $health = [
            'status' => 'healthy',
            'checks' => []
        ];

        // Database check
        try {
            $pdo->query("SELECT 1");
            $health['checks']['database'] = 'healthy';
        } catch (Exception $e) {
            $health['checks']['database'] = 'unhealthy';
            $health['status'] = 'degraded';
        }

        // Disk space check
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        $usage_percent = ($total - $free) / $total * 100;
        $health['checks']['disk_space'] = $usage_percent < 90 ? 'healthy' : 'warning';
        $health['disk_usage'] = round($usage_percent, 2) . '%';

        // Memory check
        $memory_limit = ini_get('memory_limit');
        $health['checks']['memory'] = 'healthy';
        $health['memory_limit'] = $memory_limit;

        // PHP version
        $health['php_version'] = phpversion();

        return $health;
    } catch (Exception $e) {
        return [
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ];
    }
}

?>