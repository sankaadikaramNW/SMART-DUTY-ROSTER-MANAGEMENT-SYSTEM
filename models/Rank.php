<?php
/**
 * Rank Model
 */

class Rank {

    // Retrieve list of ranks
    public static function getAll($activeOnly = false) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM ranks";
        if ($activeOnly) {
            $sql .= " WHERE status = 'Active'";
        }
        $sql .= " ORDER BY display_order ASC, rank_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve single rank
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM ranks WHERE rank_id = :rank_id");
        $stmt->execute([':rank_id' => $id]);
        return $stmt->fetch();
    }

    // Check if a rank name or code already exists to prevent duplicate rank names/codes
    public static function exists($name, $code, $excludeId = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) FROM ranks WHERE (rank_name = :name OR rank_code = :code)";
        $params = [':name' => $name, ':code' => $code];
        if ($excludeId !== null) {
            $sql .= " AND rank_id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    // Create or update rank details
    public static function save($id, $code, $name, $shortName, $displayOrder, $status) {
        $db = Database::getInstance()->getConnection();
        
        // Check for duplicates
        if (self::exists($name, $code, $id)) {
            throw new Exception("A rank with this name or code already exists.");
        }

        $prevData = $id ? self::getById($id) : null;
        $newData = [
            'rank_code' => $code,
            'rank_name' => $name,
            'rank_short_name' => $shortName,
            'display_order' => $displayOrder,
            'status' => $status
        ];

        if ($id) {
            $stmt = $db->prepare("
                UPDATE ranks 
                SET rank_code = :rank_code, rank_name = :rank_name, rank_short_name = :rank_short_name, 
                    display_order = :display_order, status = :status 
                WHERE rank_id = :rank_id
            ");
            $stmt->execute([
                ':rank_code' => $code,
                ':rank_name' => $name,
                ':rank_short_name' => $shortName,
                ':display_order' => (int)$displayOrder,
                ':status' => $status,
                ':rank_id' => $id
            ]);
            Logger::audit('Rank Management', 'Update Rank ID: ' . $id, $prevData, $newData);
        } else {
            $stmt = $db->prepare("
                INSERT INTO ranks (rank_code, rank_name, rank_short_name, display_order, status) 
                VALUES (:rank_code, :rank_name, :rank_short_name, :display_order, :status)
            ");
            $stmt->execute([
                ':rank_code' => $code,
                ':rank_name' => $name,
                ':rank_short_name' => $shortName,
                ':display_order' => (int)$displayOrder,
                ':status' => $status
            ]);
            $newId = $db->lastInsertId();
            Logger::audit('Rank Management', 'Create Rank ID: ' . $newId, null, $newData);
        }
    }
}
