<?php
/**
 * Personnel Model
 */

class Personnel {

    // Retrieve personnel profiles under base constraints
    public static function getAll($campId = null, $status = null, $isArchived = 0) {
        $db = Database::getInstance()->getConnection();
        
        // Enforce location restrictions for SNCO
        $restrictedCampId = LocationMiddleware::getCampConstraint();
        if ($restrictedCampId !== null) {
            $campId = $restrictedCampId;
        }

        $sql = "SELECT p.*, c.camp_name, r.rank_name AS `rank`, r.rank_short_name 
                FROM personnel p 
                LEFT JOIN camps c ON p.camp_id = c.camp_id 
                LEFT JOIN ranks r ON p.rank_id = r.rank_id
                WHERE p.is_archived = :is_archived";
        
        $params = [':is_archived' => $isArchived];
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
            SELECT p.*, c.camp_name, r.rank_name AS `rank`, r.rank_short_name 
            FROM personnel p 
            LEFT JOIN camps c ON p.camp_id = c.camp_id 
            LEFT JOIN ranks r ON p.rank_id = r.rank_id
            WHERE p.service_number = :service_number
        ");
        $stmt->execute([':service_number' => $serviceNumber]);
        return $stmt->fetch();
    }

    // Create or update personnel details
    public static function save($serviceNumber, $rankId, $initials, $fullName, $trade, $f1250, $section, $appointment, $campId, $contactNumber, $status, $isUpdate = false, $squadron = null, $email = null) {
        $db = Database::getInstance()->getConnection();
        
        // Validate SNCO location compliance
        if ($campId !== null) {
            LocationMiddleware::validateCamp($campId);
        }
        if ($isUpdate) {
            LocationMiddleware::validatePersonnel($serviceNumber);
        }

        $emailVal = empty($email) ? null : $email;

        $prevData = $isUpdate ? self::getByServiceNumber($serviceNumber) : null;
        $newData = [
            'service_number' => $serviceNumber,
            'rank_id' => $rankId,
            'initials' => $initials,
            'full_name' => $fullName,
            'trade' => $trade,
            'f1250' => $f1250,
            'section' => $section,
            'appointment' => $appointment,
            'squadron' => $squadron,
            'camp_id' => $campId,
            'contact_number' => $contactNumber,
            'email' => $emailVal,
            'status' => $status
        ];

        if ($isUpdate) {
            $stmt = $db->prepare("
                UPDATE personnel 
                SET rank_id = :rank_id, initials = :initials, full_name = :full_name, trade = :trade, 
                    f1250 = :f1250, section = :section, appointment = :appointment, squadron = :squadron, 
                    camp_id = :camp_id, contact_number = :contact_number, email = :email, status = :status 
                WHERE service_number = :service_number
            ");
            $stmt->execute([
                ':rank_id' => $rankId,
                ':initials' => $initials,
                ':full_name' => $fullName,
                ':trade' => $trade,
                ':f1250' => $f1250,
                ':section' => $section,
                ':appointment' => $appointment,
                ':squadron' => $squadron,
                ':camp_id' => $campId,
                ':contact_number' => $contactNumber,
                ':email' => $emailVal,
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
                INSERT INTO personnel (service_number, rank_id, initials, full_name, trade, f1250, section, appointment, squadron, camp_id, contact_number, email, status) 
                VALUES (:service_number, :rank_id, :initials, :full_name, :trade, :f1250, :section, :appointment, :squadron, :camp_id, :contact_number, :email, :status)
            ");
            $stmt->execute([
                ':service_number' => $serviceNumber,
                ':rank_id' => $rankId,
                ':initials' => $initials,
                ':full_name' => $fullName,
                ':trade' => $trade,
                ':f1250' => $f1250,
                ':section' => $section,
                ':appointment' => $appointment,
                ':squadron' => $squadron,
                ':camp_id' => $campId,
                ':contact_number' => $contactNumber,
                ':email' => $emailVal,
                ':status' => $status
            ]);
            
            // Add initial active posting log (except for admin)
            if (strtolower($serviceNumber) !== 'admin') {
                Posting::addPostingRecord($serviceNumber, $campId, $campId, date('Y-m-d'));
            }
            
            Logger::audit('Personnel Management', 'Create Personnel: ' . $serviceNumber, null, $newData);
        }
    }

    // Get consecutive days a person has stayed in the camp
    public static function getConsecutiveDaysInCamp($serviceNumber) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT check_in_date, check_out_date 
            FROM camp_attendance 
            WHERE service_number = ? 
            ORDER BY check_in_date DESC, attendance_id DESC 
            LIMIT 1
        ");
        $stmt->execute([$serviceNumber]);
        $res = $stmt->fetch();
        if ($res && $res['check_out_date'] === null) {
            $checkIn = new DateTime($res['check_in_date']);
            $today = new DateTime();
            $diff = $checkIn->diff($today);
            return $diff->days;
        }
        return 0;
    }

    // Lookup matching personnel (AJAX auto-completers)
    public static function search($query) {
        $db = Database::getInstance()->getConnection();
        $restrictedCampId = LocationMiddleware::getCampConstraint();

        $sql = "SELECT p.*, c.camp_name, rk.rank_name AS `rank`, rk.rank_short_name,
                       pos.effective_date AS posting_effective_date,
                       pos_from.camp_name AS posting_from_camp_name
                FROM personnel p 
                LEFT JOIN camps c ON p.camp_id = c.camp_id 
                LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                LEFT JOIN postings pos ON p.service_number = pos.service_number AND pos.status = 'Active'
                LEFT JOIN camps pos_from ON pos.from_camp_id = pos_from.camp_id
                WHERE p.is_archived = 0 AND (p.service_number LIKE :query1 
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
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as &$row) {
            $row['days_in_camp'] = self::getConsecutiveDaysInCamp($row['service_number']);
        }
        
        return $rows;
    }

    // Archive a personnel record and deactivate/archive their user account if it exists
    public static function archive($serviceNumber, $reason, $adminServiceNumber) {
        $db = Database::getInstance()->getConnection();
        
        $prevData = self::getByServiceNumber($serviceNumber);
        if (!$prevData) {
            throw new Exception("Personnel profile not found.");
        }

        $db->beginTransaction();
        try {
            // Archive personnel
            $stmt = $db->prepare("
                UPDATE personnel 
                SET is_archived = 1, archived_at = CURRENT_TIMESTAMP, 
                    archived_by = :archived_by, archive_reason = :archive_reason, 
                    status = 'Inactive'
                WHERE service_number = :service_number
            ");
            $stmt->execute([
                ':archived_by' => $adminServiceNumber,
                ':archive_reason' => $reason,
                ':service_number' => $serviceNumber
            ]);

            // Also archive their user account if it exists
            $stmtUser = $db->prepare("SELECT user_id FROM users WHERE service_number = :service_number");
            $stmtUser->execute([':service_number' => $serviceNumber]);
            $user = $stmtUser->fetch();
            if ($user) {
                $stmtArchiveUser = $db->prepare("
                    UPDATE users 
                    SET is_archived = 1, archived_at = CURRENT_TIMESTAMP, 
                        archived_by = :archived_by, archive_reason = :archive_reason,
                        status = 'Suspended'
                    WHERE user_id = :user_id
                ");
                $stmtArchiveUser->execute([
                    ':archived_by' => $adminServiceNumber,
                    ':archive_reason' => 'Associated personnel archived: ' . $reason,
                    ':user_id' => $user['user_id']
                ]);
            }

            $db->commit();
            
            // Log audit trail
            // Bypass location validation constraint check to retrieve details for log
            $stmtNew = $db->prepare("
                SELECT p.*, c.camp_name, r.rank_name AS `rank`, r.rank_short_name 
                FROM personnel p 
                LEFT JOIN camps c ON p.camp_id = c.camp_id 
                LEFT JOIN ranks r ON p.rank_id = r.rank_id
                WHERE p.service_number = :service_number
            ");
            $stmtNew->execute([':service_number' => $serviceNumber]);
            $newData = $stmtNew->fetch();
            
            Logger::audit('Personnel Management', 'Archive Personnel: ' . $serviceNumber, $prevData, $newData);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // Restore an archived personnel record and reactivate their user account if it exists
    public static function restore($serviceNumber, $reason, $adminServiceNumber) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT p.*, c.camp_name, r.rank_name AS `rank`, r.rank_short_name 
            FROM personnel p 
            LEFT JOIN camps c ON p.camp_id = c.camp_id 
            LEFT JOIN ranks r ON p.rank_id = r.rank_id
            WHERE p.service_number = :service_number
        ");
        $stmt->execute([':service_number' => $serviceNumber]);
        $prevData = $stmt->fetch();

        if (!$prevData) {
            throw new Exception("Personnel profile not found.");
        }

        $db->beginTransaction();
        try {
            // Restore personnel
            $stmt = $db->prepare("
                UPDATE personnel 
                SET is_archived = 0, restored_at = CURRENT_TIMESTAMP, 
                    restored_by = :restored_by, restore_reason = :restore_reason, 
                    status = 'Active'
                WHERE service_number = :service_number
            ");
            $stmt->execute([
                ':restored_by' => $adminServiceNumber,
                ':restore_reason' => $reason,
                ':service_number' => $serviceNumber
            ]);

            // Also restore/reactivate their user account if it exists
            $stmtUser = $db->prepare("SELECT user_id FROM users WHERE service_number = :service_number");
            $stmtUser->execute([':service_number' => $serviceNumber]);
            $user = $stmtUser->fetch();
            if ($user) {
                $stmtRestoreUser = $db->prepare("
                    UPDATE users 
                    SET is_archived = 0, restored_at = CURRENT_TIMESTAMP, 
                        restored_by = :restored_by, restore_reason = :restore_reason,
                        status = 'Active'
                    WHERE user_id = :user_id
                ");
                $stmtRestoreUser->execute([
                    ':restored_by' => $adminServiceNumber,
                    ':restore_reason' => 'Associated personnel restored: ' . $reason,
                    ':user_id' => $user['user_id']
                ]);
            }

            $db->commit();
            
            // Log audit trail
            $stmtNew = $db->prepare("
                SELECT p.*, c.camp_name, r.rank_name AS `rank`, r.rank_short_name 
                FROM personnel p 
                LEFT JOIN camps c ON p.camp_id = c.camp_id 
                LEFT JOIN ranks r ON p.rank_id = r.rank_id
                WHERE p.service_number = :service_number
            ");
            $stmtNew->execute([':service_number' => $serviceNumber]);
            $newData = $stmtNew->fetch();
            
            Logger::audit('Personnel Management', 'Restore Personnel: ' . $serviceNumber, $prevData, $newData);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
