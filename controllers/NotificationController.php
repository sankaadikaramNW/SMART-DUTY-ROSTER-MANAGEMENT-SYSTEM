<?php
/**
 * Notification Controller
 */

class NotificationController {

    // Fetch and display active user notifications
    public function getNotifications() {
        try {
            $serviceNum = Session::get('service_number');
            if (!$serviceNum) {
                throw new Exception("You must be logged in to view notifications.");
            }

            $notifications = Notification::getAll($serviceNum);

            $pageTitle = 'Notifications';
            include __DIR__ . '/../views/notifications/index.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/dashboard');
        }
    }

    // Mark notification as read
    public function markAsRead() {
        try {
            Security::verifyCsrf();

            $serviceNum = Session::get('service_number');
            if (!$serviceNum) {
                throw new Exception("Session expired.");
            }

            // Handle both JSON and normal POST
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (!$input) {
                $input = $_POST;
            }

            $notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : null;

            if ($notificationId) {
                Notification::markAsRead($notificationId, $serviceNum);
            } else {
                // If no specific ID, mark all as read
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE service_number = ?");
                $stmt->execute([$serviceNum]);
            }

            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                return Response::json(['success' => true]);
            } else {
                Response::redirect('/notifications');
            }
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                return Response::json(['success' => false, 'message' => $e->getMessage()], 500);
            } else {
                Session::set('error_message', $e->getMessage());
                Response::redirect('/notifications');
            }
        }
    }
}
