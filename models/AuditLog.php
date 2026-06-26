<?php
/**
 * AuditLog Model
 */

class AuditLog {

    // Retrieve system audit logs with pagination support
    public static function getAll($module = null, $user = null, $limit = 100, $offset = 0) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT a.*, p.full_name, rk.rank_name AS rank 
                FROM audit_logs a 
                LEFT JOIN personnel p ON a.service_number = p.service_number 
                LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                WHERE 1=1";
        
        $params = [];
        if ($module) {
            $sql .= " AND a.module = :module";
            $params[':module'] = $module;
        }
        if ($user) {
            $sql .= " AND (a.service_number LIKE :user OR p.full_name LIKE :user)";
            $params[':user'] = '%' . $user . '%';
        }
        
        $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($sql);
        
        // Bind parameters correctly
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Count records for pagination calculations
    public static function getCount($module = null, $user = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as cnt 
                FROM audit_logs a 
                LEFT JOIN personnel p ON a.service_number = p.service_number 
                WHERE 1=1";
        
        $params = [];
        if ($module) {
            $sql .= " AND a.module = :module";
            $params[':module'] = $module;
        }
        if ($user) {
            $sql .= " AND (a.service_number LIKE :user OR p.full_name LIKE :user)";
            $params[':user'] = '%' . $user . '%';
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return $res ? (int)$res['cnt'] : 0;
    }
}
