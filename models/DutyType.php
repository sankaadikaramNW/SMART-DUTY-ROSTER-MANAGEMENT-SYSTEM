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
        $sql .= " ORDER BY display_order ASC, duty_type_name ASC";
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
    public static function save($id, $name, $code, $description, $color, $icon, $displayOrder, $status, $userId = null) {
        $db = Database::getInstance()->getConnection();
        
        // Validation: Unique Name
        $stmtName = $db->prepare("SELECT COUNT(*) FROM duty_types WHERE duty_type_name = :name AND duty_type_id != :id");
        $stmtName->execute([':name' => $name, ':id' => (int)$id]);
        if ($stmtName->fetchColumn() > 0) {
            throw new Exception("Duty Type Name '$name' already exists.");
        }

        // Validation: Unique Code
        $code = strtoupper(trim($code));
        $stmtCode = $db->prepare("SELECT COUNT(*) FROM duty_types WHERE duty_code = :code AND duty_type_id != :id");
        $stmtCode->execute([':code' => $code, ':id' => (int)$id]);
        if ($stmtCode->fetchColumn() > 0) {
            throw new Exception("Duty Code '$code' already exists.");
        }
        
        $prevData = $id ? self::getById($id) : null;
        $newData = [
            'duty_type_name' => $name,
            'duty_code' => $code,
            'description' => $description,
            'color_code' => $color,
            'icon_class' => $icon,
            'display_order' => (int)$displayOrder,
            'status' => $status
        ];

        if ($id) {
            $stmt = $db->prepare("
                UPDATE duty_types 
                SET duty_type_name = :duty_type_name, 
                    duty_code = :duty_code,
                    description = :description, 
                    color_code = :color_code,
                    icon_class = :icon_class,
                    display_order = :display_order,
                    status = :status,
                    updated_by = :updated_by
                WHERE duty_type_id = :duty_type_id
            ");
            $stmt->execute([
                ':duty_type_name' => $name,
                ':duty_code' => $code,
                ':description' => $description,
                ':color_code' => $color,
                ':icon_class' => $icon,
                ':display_order' => (int)$displayOrder,
                ':status' => $status,
                ':updated_by' => $userId,
                ':duty_type_id' => $id
            ]);
            Logger::audit('Duty Type Management', 'Update Duty Type ID: ' . $id, $prevData, $newData);
        } else {
            $stmt = $db->prepare("
                INSERT INTO duty_types (duty_type_name, duty_code, description, color_code, icon_class, display_order, status, created_by) 
                VALUES (:duty_type_name, :duty_code, :description, :color_code, :icon_class, :display_order, :status, :created_by)
            ");
            $stmt->execute([
                ':duty_type_name' => $name,
                ':duty_code' => $code,
                ':description' => $description,
                ':color_code' => $color,
                ':icon_class' => $icon,
                ':display_order' => (int)$displayOrder,
                ':status' => $status,
                ':created_by' => $userId
            ]);
            $newId = $db->lastInsertId();
            Logger::audit('Duty Type Management', 'Create Duty Type ID: ' . $newId, null, $newData);
        }
    }
}
