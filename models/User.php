<?php
/**
 * User Model
 */

class User {

    // Authenticate a user via service number and password check
    public static function authenticate($serviceNumber, $password) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT u.*, r.role_name, p.camp_id, p.full_name, rk.rank_name AS `rank` 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            JOIN personnel p ON u.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            WHERE u.service_number = :service_number AND u.status = 'Active' AND p.status = 'Active'
        ");
        $stmt->execute([':service_number' => $serviceNumber]);
        $user = $stmt->fetch();
        
        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    // Get list of all accounts for Admin review
    public static function getAll($campId = null, $isArchived = 0) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT u.*, r.role_name, p.full_name, rk.rank_name AS `rank`, p.camp_id, c.camp_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            JOIN personnel p ON u.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            LEFT JOIN camps c ON p.camp_id = c.camp_id
            WHERE u.is_archived = :is_archived
        ";
        
        $params = [':is_archived' => $isArchived];
        if ($campId !== null) {
            $sql .= " AND p.camp_id = :camp_id";
            $params[':camp_id'] = $campId;
        }
        
        $sql .= " ORDER BY u.user_id ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Retrieve dynamic profile details by joining users, personnel, ranks, and camps
     */
    public static function getProfileInfo($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT u.user_id, p.initials, p.full_name, rk.rank_short_name, rk.rank_name, c.camp_name
            FROM users u
            JOIN personnel p ON u.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            LEFT JOIN camps c ON p.camp_id = c.camp_id
            WHERE u.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }

    // Retrieve single user ID
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $id]);
        return $stmt->fetch();
    }

    // Create or update user credentials and role access
    public static function save($id, $serviceNumber, $password, $roleId, $status) {
        $db = Database::getInstance()->getConnection();
        
        $prevData = $id ? self::getById($id) : null;
        $newData = [
            'service_number' => $serviceNumber,
            'role_id' => $roleId,
            'status' => $status
        ];

        if ($id) {
            if (!empty($password)) {
                $hash = Security::hashPassword($password);
                $stmt = $db->prepare("
                    UPDATE users 
                    SET service_number = :service_number, password_hash = :hash, role_id = :role_id, status = :status 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':service_number' => $serviceNumber,
                    ':hash' => $hash,
                    ':role_id' => $roleId,
                    ':status' => $status,
                    ':user_id' => $id
                ]);
            } else {
                $stmt = $db->prepare("
                    UPDATE users 
                    SET service_number = :service_number, role_id = :role_id, status = :status 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':service_number' => $serviceNumber,
                    ':role_id' => $roleId,
                    ':status' => $status,
                    ':user_id' => $id
                ]);
            }
            Logger::audit('User Management', 'Update User ID: ' . $id, $prevData, $newData);
        } else {
            $hash = Security::hashPassword($password);
            $stmt = $db->prepare("
                INSERT INTO users (service_number, password_hash, role_id, status) 
                VALUES (:service_number, :hash, :role_id, :status)
            ");
            $stmt->execute([
                ':service_number' => $serviceNumber,
                ':hash' => $hash,
                ':role_id' => $roleId,
                ':status' => $status
            ]);
            $newId = $db->lastInsertId();
            Logger::audit('User Management', 'Create User ID: ' . $newId, null, $newData);
        }
    }

    // Toggle active status (Suspend/Activate)
    public static function setStatus($id, $status) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($id);
        $stmt = $db->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
        $stmt->execute([':status' => $status, ':user_id' => $id]);
        Logger::audit('User Management', 'Toggle status of User ID: ' . $id . ' to ' . $status, $prevData, ['status' => $status]);
    }

    // Update password (Self service/forced reset)
    public static function updatePassword($userId, $newPassword) {
        $db = Database::getInstance()->getConnection();
        $hash = Security::hashPassword($newPassword);
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash, force_password_change = 0 WHERE user_id = :user_id");
        $stmt->execute([
            ':hash' => $hash,
            ':user_id' => $userId
        ]);
        Logger::audit('Account Security', 'Password updated for User ID: ' . $userId);
    }

    // Retrieve user by service number (includes inactive, locked, or archived)
    public static function getByServiceNumber($serviceNumber) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT u.*, r.role_name, p.camp_id, p.full_name, rk.rank_name AS `rank`, p.status AS personnel_status, p.is_archived AS personnel_is_archived, c.camp_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            JOIN personnel p ON u.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            LEFT JOIN camps c ON p.camp_id = c.camp_id
            WHERE u.service_number = :service_number
        ");
        $stmt->execute([':service_number' => $serviceNumber]);
        return $stmt->fetch();
    }

    // Archive a user account
    public static function archive($userId, $reason, $adminServiceNumber) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($userId);
        if (!$prevData) {
            throw new Exception("User account not found.");
        }
        
        $stmt = $db->prepare("
            UPDATE users 
            SET is_archived = 1, archived_at = CURRENT_TIMESTAMP, 
                archived_by = :archived_by, archive_reason = :archive_reason,
                status = 'Suspended'
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':archived_by' => $adminServiceNumber,
            ':archive_reason' => $reason,
            ':user_id' => $userId
        ]);
        
        $newData = self::getById($userId);
        Logger::audit('User Management', 'Archive User ID: ' . $userId, $prevData, $newData);
    }

    // Restore a user account
    public static function restore($userId, $reason, $adminServiceNumber) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($userId);
        if (!$prevData) {
            throw new Exception("User account not found.");
        }
        
        $stmt = $db->prepare("
            UPDATE users 
            SET is_archived = 0, restored_at = CURRENT_TIMESTAMP, 
                restored_by = :restored_by, restore_reason = :restore_reason,
                status = 'Active'
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':restored_by' => $adminServiceNumber,
            ':restore_reason' => $reason,
            ':user_id' => $userId
        ]);
        
        $newData = self::getById($userId);
        Logger::audit('User Management', 'Restore User ID: ' . $userId, $prevData, $newData);
    }

    // Lock a user account
    public static function lock($userId, $reason) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($userId);
        $stmt = $db->prepare("
            UPDATE users 
            SET status = 'Locked', lock_date = CURRENT_TIMESTAMP, lock_reason = :reason 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':reason' => $reason, ':user_id' => $userId]);
        $newData = self::getById($userId);
        Logger::audit('User Management', 'Account Locked (User ID: ' . $userId . '): ' . $reason, $prevData, $newData);
    }

    // Unlock a user account
    public static function unlock($userId, $reason, $adminServiceNumber) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($userId);
        $stmt = $db->prepare("
            UPDATE users 
            SET status = 'Active', failed_attempts = 0, locked_until = NULL, 
                lock_date = NULL, lock_reason = NULL, unlock_reason = :reason, 
                unlock_date = CURRENT_TIMESTAMP, unlocked_by = :unlocked_by 
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':reason' => $reason, 
            ':unlocked_by' => $adminServiceNumber,
            ':user_id' => $userId
        ]);
        $newData = self::getById($userId);
        Logger::audit('User Management', 'Account Unlocked (User ID: ' . $userId . '): ' . $reason, $prevData, $newData);
    }

    // Reset a user password
    public static function resetPassword($userId, $tempPassword, $reason, $adminServiceNumber) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($userId);
        if (!$prevData) {
            throw new Exception("User account not found.");
        }
        
        $hash = Security::hashPassword($tempPassword);
        $stmt = $db->prepare("
            UPDATE users 
            SET password_hash = :hash, force_password_change = 1, 
                password_reset_at = CURRENT_TIMESTAMP, password_reset_by = :reset_by, 
                password_reset_reason = :reason 
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':hash' => $hash,
            ':reset_by' => $adminServiceNumber,
            ':reason' => $reason,
            ':user_id' => $userId
        ]);
        
        $newData = self::getById($userId);
        Logger::audit('User Management', 'Password Reset (User ID: ' . $userId . '): ' . $reason, $prevData, $newData);
    }

    // Increment failed login attempts
    public static function incrementFailedAttempts($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}
