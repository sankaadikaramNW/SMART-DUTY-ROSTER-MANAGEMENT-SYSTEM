<?php
/**
 * Roster Model
 */

class Roster {

    // Retrieve list of rosters
    public static function getAll($campId = null, $status = null) {
        $db = Database::getInstance()->getConnection();
        
        $restrictedCampId = LocationMiddleware::getCampConstraint();
        if ($restrictedCampId !== null) {
            $campId = $restrictedCampId;
        }

        $sql = "SELECT r.*, c.camp_name, p.full_name AS creator_name, p.rank AS creator_rank
                FROM duty_rosters r
                JOIN camps c ON r.camp_id = c.camp_id
                JOIN users u ON r.created_by = u.user_id
                JOIN personnel p ON u.service_number = p.service_number
                WHERE 1=1";
        
        $params = [];
        if ($campId !== null) {
            $sql .= " AND r.camp_id = :camp_id";
            $params[':camp_id'] = $campId;
        }
        if ($status !== null) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY r.start_date DESC, r.roster_id DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Retrieve single roster record
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT r.*, c.camp_name, p.full_name AS creator_name, p.rank AS creator_rank
            FROM duty_rosters r
            JOIN camps c ON r.camp_id = c.camp_id
            JOIN users u ON r.created_by = u.user_id
            JOIN personnel p ON u.service_number = p.service_number
            WHERE r.roster_id = :roster_id
        ");
        $stmt->execute([':roster_id' => $id]);
        $roster = $stmt->fetch();

        if ($roster) {
            // Validate location restriction if necessary (e.g. for SNCO access)
            LocationMiddleware::validateCamp($roster['camp_id']);
        }
        return $roster;
    }

    // Create a new roster record
    public static function create($name, $campId, $startDate, $endDate, $createdBy) {
        $db = Database::getInstance()->getConnection();
        
        LocationMiddleware::validateCamp($campId);

        $stmt = $db->prepare("
            INSERT INTO duty_rosters (roster_name, camp_id, start_date, end_date, status, created_by)
            VALUES (:name, :camp_id, :start_date, :end_date, 'Draft', :created_by)
        ");
        $stmt->execute([
            ':name' => $name,
            ':camp_id' => $campId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':created_by' => $createdBy
        ]);
        
        $newId = $db->lastInsertId();
        
        $newData = [
            'roster_id' => $newId,
            'roster_name' => $name,
            'camp_id' => $campId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'Draft',
            'created_by' => $createdBy
        ];
        Logger::audit('Roster Management', 'Create Roster ID: ' . $newId, null, $newData);
        
        return $newId;
    }

    // Update roster status
    public static function updateStatus($id, $status) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($id);
        if (!$prevData) {
            throw new Exception("Roster not found.");
        }
        
        LocationMiddleware::validateCamp($prevData['camp_id']);

        $stmt = $db->prepare("UPDATE duty_rosters SET status = :status WHERE roster_id = :roster_id");
        $stmt->execute([
            ':status' => $status,
            ':roster_id' => $id
        ]);

        Logger::audit('Roster Management', 'Update Status of Roster ID: ' . $id . ' to ' . $status, $prevData, ['status' => $status]);
    }

    // Delete a roster
    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($id);
        if (!$prevData) {
            throw new Exception("Roster not found.");
        }
        
        LocationMiddleware::validateCamp($prevData['camp_id']);

        $stmt = $db->prepare("DELETE FROM duty_rosters WHERE roster_id = :roster_id");
        $stmt->execute([':roster_id' => $id]);

        Logger::audit('Roster Management', 'Delete Roster ID: ' . $id, $prevData, null);
    }
}
