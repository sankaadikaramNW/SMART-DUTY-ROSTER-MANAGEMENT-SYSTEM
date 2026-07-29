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
        $sql .= " ORDER BY display_order ASC, shift_name ASC";
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
    public static function save($id, $name, $code, $description, $displayOrder, $status) {
        $db = Database::getInstance()->getConnection();
        
        $prevData = $id ? self::getById($id) : null;
        $newData = [
            'shift_name' => $name,
            'shift_code' => $code,
            'description' => $description,
            'display_order' => $displayOrder,
            'status' => $status
        ];

        if ($id) {
            $stmt = $db->prepare("
                UPDATE shifts 
                SET shift_name = :shift_name, shift_code = :shift_code, 
                    description = :description, display_order = :display_order, status = :status,
                    updated_by = :updated_by
                WHERE shift_id = :shift_id
            ");
            $stmt->execute([
                ':shift_name' => $name,
                ':shift_code' => $code,
                ':description' => $description,
                ':display_order' => $displayOrder,
                ':status' => $status,
                ':updated_by' => Session::get('user_id'),
                ':shift_id' => $id
            ]);
            Logger::audit('Shift Management', 'Update Shift ID: ' . $id, $prevData, $newData);
        } else {
            $stmt = $db->prepare("
                INSERT INTO shifts (shift_name, shift_code, description, display_order, status, created_by) 
                VALUES (:shift_name, :shift_code, :description, :display_order, :status, :created_by)
            ");
            $stmt->execute([
                ':shift_name' => $name,
                ':shift_code' => $code,
                ':description' => $description,
                ':display_order' => $displayOrder,
                ':status' => $status,
                ':created_by' => Session::get('user_id')
            ]);
            $newId = $db->lastInsertId();
            Logger::audit('Shift Management', 'Create Shift ID: ' . $newId, null, $newData);
        }
    }
}
