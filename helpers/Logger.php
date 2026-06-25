<?php
/**
 * System Logger & Immutable Audit Trail Helper
 */

class Logger {

    // Log general debug/error entries to file fallback
    public static function log($message, $level = 'INFO') {
        $logDir = __DIR__ . '/../logs/';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . 'app.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getIp();
        $formatted = "[$timestamp] [$level] [$ip] $message" . PHP_EOL;
        file_put_contents($logFile, $formatted, FILE_APPEND);
    }

    // Insert immutable audit logging
    public static function audit($module, $action, $previousData = null, $newData = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO audit_logs 
                (user_id, service_number, module, action, previous_data, new_data, ip_address, user_agent) 
                VALUES (:user_id, :service_number, :module, :action, :previous_data, :new_data, :ip_address, :user_agent)
            ");
            
            $userId = Session::get('user_id');
            $serviceNumber = Session::get('service_number');
            $ipAddress = self::getIp();
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
            
            $prevJson = $previousData ? json_encode($previousData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newData ? json_encode($newData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
            
            $stmt->execute([
                ':user_id' => $userId,
                ':service_number' => $serviceNumber,
                ':module' => $module,
                ':action' => $action,
                ':previous_data' => $prevJson,
                ':new_data' => $newJson,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent
            ]);
        } catch (Exception $e) {
            self::log("Failed to insert audit log: " . $e->getMessage(), 'ERROR');
        }
    }

    // Track user logins
    public static function loginAttempt($serviceNumber, $status, $remarks = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO login_history 
                (service_number, ip_address, user_agent, status, remarks) 
                VALUES (:service_number, :ip_address, :user_agent, :status, :remarks)
            ");
            $ipAddress = self::getIp();
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
            
            $stmt->execute([
                ':service_number' => $serviceNumber,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
                ':status' => $status,
                ':remarks' => $remarks
            ]);
        } catch (Exception $e) {
            self::log("Failed to insert login history: " . $e->getMessage(), 'ERROR');
        }
    }

    // Capture standard IP addresses
    private static function getIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain a comma separated list of IPs
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } else {
            return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        }
    }
}
