<?php
/**
 * Shift Model
 */

class Shift {

    // Retrieve shifts
    public static function getAll($activeOnly = false) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM shifts";
        if ($activeOnly) {
            $sql .= " WHERE status = 'Active'";
        }
        $sql .= " ORDER BY start_time ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve single shift
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM shifts WHERE shift_id = :shift_id");
        $stmt->execute([':shift_id' => $id]);
        return $stmt->fetch();
    }

    // Save shift profile
    public static function save($id, $name, $startTime, $endTime, $duration, $description, $status) {
        $db = Database::getInstance()->getConnection();
        
        $prevData = $id ? self::getById($id) : null;
        $newData = [
            'shift_name' => $name,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_hours' => $duration,
            'description' => $description,
            'status' => $status
        ];

        if ($id) {
            $stmt = $db->prepare("
                UPDATE shifts 
                SET shift_name = :shift_name, start_time = :start_time, end_time = :end_time, 
                    duration_hours = :duration_hours, description = :description, status = :status 
                WHERE shift_id = :shift_id
            ");
            $stmt->execute([
                ':shift_name' => $name,
                ':start_time' => $startTime,
                ':end_time' => $endTime,
                ':duration_hours' => $duration,
                ':description' => $description,
                ':status' => $status,
                ':shift_id' => $id
            ]);
            Logger::audit('Shift Management', 'Update Shift ID: ' . $id, $prevData, $newData);
        } else {
            $stmt = $db->prepare("
                INSERT INTO shifts (shift_name, start_time, end_time, duration_hours, description, status) 
                VALUES (:shift_name, :start_time, :end_time, :duration_hours, :description, :status)
            ");
            $stmt->execute([
                ':shift_name' => $name,
                ':start_time' => $startTime,
                ':end_time' => $endTime,
                ':duration_hours' => $duration,
                ':description' => $description,
                ':status' => $status
            ]);
            $newId = $db->lastInsertId();
            Logger::audit('Shift Management', 'Create Shift ID: ' . $newId, null, $newData);
        }
    }
}
