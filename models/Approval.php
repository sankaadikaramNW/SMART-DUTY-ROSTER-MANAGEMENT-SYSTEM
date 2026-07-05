<?php
/**
 * Approval Model
 */

class Approval {

    // Log approval history action
    public static function add($rosterId, $actionBy, $action, $remarks = null) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO approvals (roster_id, action_by, action, remarks)
            VALUES (:roster_id, :action_by, :action, :remarks)
        ");
        $stmt->execute([
            ':roster_id' => $rosterId,
            ':action_by' => $actionBy,
            ':action' => $action,
            ':remarks' => $remarks
        ]);
        
        $newId = $db->lastInsertId();

        $newData = [
            'roster_id' => $rosterId,
            'action_by' => $actionBy,
            'action' => $action,
            'remarks' => $remarks
        ];
        Logger::audit('Roster Approval', 'Log Action: ' . $action . ' on Roster ID: ' . $rosterId, null, $newData);
        
        return $newId;
    }

    // Retrieve approval history logs for a roster
    public static function getByRosterId($rosterId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT a.*, rk.rank_name AS `rank`, p.initials, p.full_name, r.role_name
            FROM approvals a
            JOIN users u ON a.action_by = u.user_id
            JOIN roles r ON u.role_id = r.role_id
            JOIN personnel p ON u.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            WHERE a.roster_id = :roster_id
            ORDER BY a.created_at ASC
        ");
        $stmt->execute([':roster_id' => $rosterId]);
        return $stmt->fetchAll();
    }
}
