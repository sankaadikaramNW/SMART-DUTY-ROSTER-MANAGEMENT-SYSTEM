<?php
/**
 * DutyType Model
 */

class DutyType {

    // Retrieve duty types
    public static function getAll($activeOnly = false) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM duty_types";
        if ($activeOnly) {
            $sql .= " WHERE status = 'Active'";
        }
        $sql .= " ORDER BY duty_type_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve single duty type
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM duty_types WHERE duty_type_id = :duty_type_id");
        $stmt->execute([':duty_type_id' => $id]);
        return $stmt->fetch();
    }

    // Save duty type
    public static function save($id, $name, $description, $status) {
        $db = Database::getInstance()->getConnection();
        
        $prevData = $id ? self::getById($id) : null;
        $newData = [
            'duty_type_name' => $name,
            'description' => $description,
            'status' => $status
        ];

        if ($id) {
            $stmt = $db->prepare("
                UPDATE duty_types 
                SET duty_type_name = :duty_type_name, description = :description, status = :status 
                WHERE duty_type_id = :duty_type_id
            ");
            $stmt->execute([
                ':duty_type_name' => $name,
                ':description' => $description,
                ':status' => $status,
                ':duty_type_id' => $id
            ]);
            Logger::audit('Duty Type Management', 'Update Duty Type ID: ' . $id, $prevData, $newData);
        } else {
            $stmt = $db->prepare("
                INSERT INTO duty_types (duty_type_name, description, status) 
                VALUES (:duty_type_name, :description, :status)
            ");
            $stmt->execute([
                ':duty_type_name' => $name,
                ':description' => $description,
                ':status' => $status
            ]);
            $newId = $db->lastInsertId();
            Logger::audit('Duty Type Management', 'Create Duty Type ID: ' . $newId, null, $newData);
        }
    }
}
