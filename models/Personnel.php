<?php
/**
 * Personnel Model
 */

class Personnel {

    // Retrieve personnel profiles under base constraints
    public static function getAll($campId = null, $status = null) {
        $db = Database::getInstance()->getConnection();
        
        // Enforce location restrictions for SNCO
        $restrictedCampId = LocationMiddleware::getCampConstraint();
        if ($restrictedCampId !== null) {
            $campId = $restrictedCampId;
        }

        $sql = "SELECT p.*, c.camp_name, r.rank_name AS rank, r.rank_short_name 
                FROM personnel p 
                LEFT JOIN camps c ON p.camp_id = c.camp_id 
                LEFT JOIN ranks r ON p.rank_id = r.rank_id
                WHERE 1=1";
        
        $params = [];
        if ($campId !== null) {
            $sql .= " AND p.camp_id = :camp_id";
            $params[':camp_id'] = $campId;
        }
        if ($status !== null) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY r.display_order ASC, p.service_number ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Retrieve single personnel record
    public static function getByServiceNumber($serviceNumber) {
        $db = Database::getInstance()->getConnection();
        
        // Enforce access permission check
        LocationMiddleware::validatePersonnel($serviceNumber);

        $stmt = $db->prepare("
            SELECT p.*, c.camp_name, r.rank_name AS rank, r.rank_short_name 
            FROM personnel p 
            LEFT JOIN camps c ON p.camp_id = c.camp_id 
            LEFT JOIN ranks r ON p.rank_id = r.rank_id
            WHERE p.service_number = :service_number
        ");
        $stmt->execute([':service_number' => $serviceNumber]);
        return $stmt->fetch();
    }

    // Create or update personnel details
    public static function save($serviceNumber, $rankId, $initials, $fullName, $trade, $squadron, $campId, $contactNumber, $email, $status, $isUpdate = false) {
        $db = Database::getInstance()->getConnection();
        
        // Validate SNCO location compliance
        if ($campId !== null) {
            LocationMiddleware::validateCamp($campId);
        }
        if ($isUpdate) {
            LocationMiddleware::validatePersonnel($serviceNumber);
        }

        $prevData = $isUpdate ? self::getByServiceNumber($serviceNumber) : null;
        $newData = [
            'service_number' => $serviceNumber,
            'rank_id' => $rankId,
            'initials' => $initials,
            'full_name' => $fullName,
            'trade' => $trade,
            'squadron' => $squadron,
            'camp_id' => $campId,
            'contact_number' => $contactNumber,
            'email' => $email,
            'status' => $status
        ];

        if ($isUpdate) {
            $stmt = $db->prepare("
                UPDATE personnel 
                SET rank_id = :rank_id, initials = :initials, full_name = :full_name, trade = :trade, 
                    squadron = :squadron, camp_id = :camp_id, contact_number = :contact_number, 
                    email = :email, status = :status 
                WHERE service_number = :service_number
            ");
            $stmt->execute([
                ':rank_id' => $rankId,
                ':initials' => $initials,
                ':full_name' => $fullName,
                ':trade' => $trade,
                ':squadron' => $squadron,
                ':camp_id' => $campId,
                ':contact_number' => $contactNumber,
                ':email' => $email,
                ':status' => $status,
                ':service_number' => $serviceNumber
            ]);
            
            // Detect camp posting movement and track automatically (except for admin)
            if (strtolower($serviceNumber) !== 'admin' && $prevData && (int)$prevData['camp_id'] !== (int)$campId) {
                Posting::addPostingRecord($serviceNumber, $prevData['camp_id'], $campId, date('Y-m-d'));
            }
            
            Logger::audit('Personnel Management', 'Update Personnel: ' . $serviceNumber, $prevData, $newData);
        } else {
            $stmt = $db->prepare("
                INSERT INTO personnel (service_number, rank_id, initials, full_name, trade, squadron, camp_id, contact_number, email, status) 
                VALUES (:service_number, :rank_id, :initials, :full_name, :trade, :squadron, :camp_id, :contact_number, :email, :status)
            ");
            $stmt->execute([
                ':service_number' => $serviceNumber,
                ':rank_id' => $rankId,
                ':initials' => $initials,
                ':full_name' => $fullName,
                ':trade' => $trade,
                ':squadron' => $squadron,
                ':camp_id' => $campId,
                ':contact_number' => $contactNumber,
                ':email' => $email,
                ':status' => $status
            ]);
            
            // Add initial active posting log (except for admin)
            if (strtolower($serviceNumber) !== 'admin') {
                Posting::addPostingRecord($serviceNumber, $campId, $campId, date('Y-m-d'));
            }
            
            Logger::audit('Personnel Management', 'Create Personnel: ' . $serviceNumber, null, $newData);
        }
    }

    // Lookup matching personnel (AJAX auto-completers)
    public static function search($query) {
        $db = Database::getInstance()->getConnection();
        $restrictedCampId = LocationMiddleware::getCampConstraint();

        $sql = "SELECT p.*, c.camp_name, rk.rank_name AS rank, rk.rank_short_name,
                       pos.effective_date AS posting_effective_date,
                       pos_from.camp_name AS posting_from_camp_name
                FROM personnel p 
                LEFT JOIN camps c ON p.camp_id = c.camp_id 
                LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                LEFT JOIN postings pos ON p.service_number = pos.service_number AND pos.status = 'Active'
                LEFT JOIN camps pos_from ON pos.from_camp_id = pos_from.camp_id
                WHERE (p.service_number LIKE :query1 
                       OR p.full_name LIKE :query2 
                       OR p.trade LIKE :query3 
                       OR rk.rank_name LIKE :query4 
                       OR rk.rank_short_name LIKE :query5)";
        
        $params = [
            ':query1' => '%' . $query . '%',
            ':query2' => '%' . $query . '%',
            ':query3' => '%' . $query . '%',
            ':query4' => '%' . $query . '%',
            ':query5' => '%' . $query . '%',
        ];
        if ($restrictedCampId !== null) {
            $sql .= " AND p.camp_id = :camp_id";
            $params[':camp_id'] = $restrictedCampId;
        }

        $sql .= " ORDER BY rk.display_order ASC, p.service_number ASC LIMIT 25";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
