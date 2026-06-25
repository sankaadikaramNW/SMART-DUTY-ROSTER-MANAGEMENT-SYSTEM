<?php
/**
 * Notification Model
 */

class Notification {

    // Queue alert message
    public static function add($serviceNumber, $title, $message) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO notifications (service_number, title, message, is_read) 
                VALUES (:service_number, :title, :message, 0)
            ");
            $stmt->execute([
                ':service_number' => $serviceNumber,
                ':title' => $title,
                ':message' => $message
            ]);
        } catch (Exception $e) {
            Logger::log("Failed to insert notification: " . $e->getMessage(), 'ERROR');
        }
    }

    // Retrieve active unread list
    public static function getUnread($serviceNumber) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM notifications 
            WHERE service_number = :service_number AND is_read = 0 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':service_number' => $serviceNumber]);
        return $stmt->fetchAll();
    }

    // Retrieve notifications (capped at latest 40)
    public static function getAll($serviceNumber) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM notifications 
            WHERE service_number = :service_number 
            ORDER BY created_at DESC LIMIT 40
        ");
        $stmt->execute([':service_number' => $serviceNumber]);
        return $stmt->fetchAll();
    }

    // Toggle read state
    public static function markAsRead($notificationId, $serviceNumber) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE notification_id = :id AND service_number = :service_number
        ");
        $stmt->execute([
            ':id' => $notificationId,
            ':service_number' => $serviceNumber
        ]);
    }
}
