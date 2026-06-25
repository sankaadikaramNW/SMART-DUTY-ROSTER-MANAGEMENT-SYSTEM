<?php
/**
 * Camp Model
 */

class Camp {

    // Retrieve list of camps
    public static function getAll($activeOnly = false) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM camps";
        if ($activeOnly) {
            $sql .= " WHERE status = 'Active'";
        }
        $sql .= " ORDER BY camp_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve single camp
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM camps WHERE camp_id = :camp_id");
        $stmt->execute([':camp_id' => $id]);
        return $stmt->fetch();
    }

    // Create or update camp details
    public static function save($id, $code, $name, $address, $status) {
        $db = Database::getInstance()->getConnection();
        
        $prevData = $id ? self::getById($id) : null;
        $newData = [
            'camp_code' => $code,
            'camp_name' => $name,
            'address' => $address,
            'status' => $status
        ];

        if ($id) {
            $stmt = $db->prepare("
                UPDATE camps 
                SET camp_code = :camp_code, camp_name = :camp_name, address = :address, status = :status 
                WHERE camp_id = :camp_id
            ");
            $stmt->execute([
                ':camp_code' => $code,
                ':camp_name' => $name,
                ':address' => $address,
                ':status' => $status,
                ':camp_id' => $id
            ]);
            Logger::audit('Camp Management', 'Update Camp ID: ' . $id, $prevData, $newData);
        } else {
            $stmt = $db->prepare("
                INSERT INTO camps (camp_code, camp_name, address, status) 
                VALUES (:camp_code, :camp_name, :address, :status)
            ");
            $stmt->execute([
                ':camp_code' => $code,
                ':camp_name' => $name,
                ':address' => $address,
                ':status' => $status
            ]);
            $newId = $db->lastInsertId();
            Logger::audit('Camp Management', 'Create Camp ID: ' . $newId, null, $newData);
        }
    }
}
