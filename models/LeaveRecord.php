<?php
/**
 * LeaveRecord Model
 */

class LeaveRecord {

    // Calculate dynamic status based on reporting and extension dates
    public static function calculateStatus($l) {
        $today = strtotime(date('Y-m-d'));
        $end = strtotime($l['granted_end_date'] ?? $l['leave_end_date']);
        $start = strtotime($l['leave_start_date']);
        $actual = $l['actual_reporting_date'] ? strtotime($l['actual_reporting_date']) : null;
        
        if ($actual !== null) {
            if ($actual <= $end) {
                return 'Completed';
            } else {
                return 'Late Reported';
            }
        } else {
            if ($today > $end) {
                return 'Not Reported';
            } else {
                if ($l['granted_end_date'] !== null) {
                    return 'Granted';
                }
                return 'Expected';
            }
        }
    }

    // Retrieve active leave records that overlap with a date range
    public static function getActiveLeavesRange($startDate, $endDate) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT lr.*, p.full_name, rk.rank_name AS `rank`, rk.rank_short_name
            FROM leave_records lr
            JOIN personnel p ON lr.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            WHERE lr.leave_start_date <= :end_date AND COALESCE(lr.granted_end_date, lr.leave_end_date) >= :start_date
            ORDER BY lr.leave_start_date ASC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['status'] = self::calculateStatus($row);
        }
        return $rows;
    }

    // Save a new leave entry
    public static function saveLeave($serviceNumber, $startDate, $endDate, $leaveType, $approvedBy) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO leave_records (service_number, leave_start_date, leave_end_date, leave_type, approved_by) 
            VALUES (:service_number, :leave_start_date, :leave_end_date, :leave_type, :approved_by)
        ");
        $stmt->execute([
            ':service_number' => $serviceNumber,
            ':leave_start_date' => $startDate,
            ':leave_end_date' => $endDate,
            ':leave_type' => $leaveType,
            ':approved_by' => $approvedBy
        ]);
        
        // Audit log leave creation
        Logger::audit('Leave Management', 'Create Leave for: ' . $serviceNumber, null, [
            'service_number' => $serviceNumber,
            'leave_start_date' => $startDate,
            'leave_end_date' => $endDate,
            'leave_type' => $leaveType,
            'approved_by' => $approvedBy
        ]);
    }

    // Mark individual as reported back
    public static function reportReturn($leaveId, $date) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM leave_records WHERE leave_id = ?");
        $stmt->execute([$leaveId]);
        $prevData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prevData) {
            throw new Exception("Leave record not found.");
        }
        
        $stmt = $db->prepare("
            UPDATE leave_records 
            SET actual_reporting_date = :actual_reporting_date 
            WHERE leave_id = :leave_id
        ");
        $stmt->execute([
            ':actual_reporting_date' => $date,
            ':leave_id' => $leaveId
        ]);
        
        $newData = $prevData;
        $newData['actual_reporting_date'] = $date;
        
        Logger::audit('Leave Management', 'Report Return for Leave ID: ' . $leaveId, $prevData, $newData);
    }

    // Grant leave extension
    public static function grantExtension($leaveId, $extendedDate, $reason, $grantedBy) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM leave_records WHERE leave_id = ?");
        $stmt->execute([$leaveId]);
        $prevData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prevData) {
            throw new Exception("Leave record not found.");
        }
        
        $stmt = $db->prepare("
            UPDATE leave_records 
            SET granted_end_date = :granted_end_date, 
                granted_by = :granted_by, 
                granted_reason = :granted_reason, 
                granted_at = NOW() 
            WHERE leave_id = :leave_id
        ");
        $stmt->execute([
            ':granted_end_date' => $extendedDate,
            ':granted_by' => $grantedBy,
            ':granted_reason' => $reason,
            ':leave_id' => $leaveId
        ]);
        
        $newData = $prevData;
        $newData['granted_end_date'] = $extendedDate;
        $newData['granted_by'] = $grantedBy;
        $newData['granted_reason'] = $reason;
        $newData['granted_at'] = date('Y-m-d H:i:s');
        
        Logger::audit('Leave Management', 'Grant Leave Extension for Leave ID: ' . $leaveId, $prevData, $newData);
    }

    // Get overdue leave periods count
    public static function getOverdueLeaveCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT COUNT(*) 
            FROM leave_records 
            WHERE actual_reporting_date IS NULL 
              AND CURDATE() > COALESCE(granted_end_date, leave_end_date)
        ");
        return (int)$stmt->fetchColumn();
    }

    // Get list of currently checked-in personnel with consecutive days in camp
    public static function getPersonnelStays() {
        $db = Database::getInstance()->getConnection();
        
        // Find latest camp attendance record for each personnel
        $stmt = $db->query("
            SELECT p.service_number, p.full_name, rk.rank_short_name AS `rank`, c.camp_name, ca.check_in_date, ca.check_out_date,
                   DATEDIFF(CURDATE(), ca.check_in_date) AS days_in_camp
            FROM personnel p
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            LEFT JOIN camps c ON p.camp_id = c.camp_id
            JOIN camp_attendance ca ON p.service_number = ca.service_number
            WHERE p.is_archived = 0 AND ca.attendance_id = (
                SELECT MAX(ca2.attendance_id)
                FROM camp_attendance ca2
                WHERE ca2.service_number = p.service_number
            )
            AND ca.check_out_date IS NULL
            ORDER BY days_in_camp DESC, p.service_number ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all leave history with personnel details
    public static function getAllWithPersonnel() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT lr.*, p.full_name, rk.rank_short_name AS `rank`, c.camp_name
            FROM leave_records lr
            JOIN personnel p ON lr.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            LEFT JOIN camps c ON p.camp_id = c.camp_id
            ORDER BY lr.leave_start_date DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['status'] = self::calculateStatus($row);
        }
        return $rows;
    }

    // Retrieve single leave record by ID
    public static function getById($leaveId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT lr.*, p.full_name, rk.rank_short_name AS `rank`, p.camp_id
            FROM leave_records lr
            JOIN personnel p ON lr.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            WHERE lr.leave_id = :leave_id
        ");
        $stmt->execute([':leave_id' => $leaveId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update an existing leave record
    public static function updateLeave($leaveId, $serviceNumber, $startDate, $endDate, $leaveType, $actualReportingDate, $grantedEndDate, $grantedReason, $currentUserServiceNumber) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM leave_records WHERE leave_id = ?");
        $stmt->execute([$leaveId]);
        $prevData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prevData) {
            throw new Exception("Leave record not found.");
        }

        // Validate SNCO location compliance
        LocationMiddleware::validatePersonnel($serviceNumber);
        LocationMiddleware::validatePersonnel($prevData['service_number']);

        // Determine extension fields
        $grantedBy = $prevData['granted_by'];
        $grantedAt = $prevData['granted_at'];

        if (!empty($grantedEndDate)) {
            if ($prevData['granted_end_date'] !== $grantedEndDate) {
                $grantedBy = $currentUserServiceNumber;
                $grantedAt = date('Y-m-d H:i:s');
            }
        } else {
            $grantedEndDate = null;
            $grantedBy = null;
            $grantedReason = null;
            $grantedAt = null;
        }

        $stmt = $db->prepare("
            UPDATE leave_records 
            SET service_number = :service_number,
                leave_start_date = :leave_start_date,
                leave_end_date = :leave_end_date,
                leave_type = :leave_type,
                actual_reporting_date = :actual_reporting_date,
                granted_end_date = :granted_end_date,
                granted_by = :granted_by,
                granted_reason = :granted_reason,
                granted_at = :granted_at
            WHERE leave_id = :leave_id
        ");
        
        $stmt->execute([
            ':service_number' => $serviceNumber,
            ':leave_start_date' => $startDate,
            ':leave_end_date' => $endDate,
            ':leave_type' => $leaveType,
            ':actual_reporting_date' => !empty($actualReportingDate) ? $actualReportingDate : null,
            ':granted_end_date' => $grantedEndDate,
            ':granted_by' => $grantedBy,
            ':granted_reason' => $grantedReason,
            ':granted_at' => $grantedAt,
            ':leave_id' => $leaveId
        ]);

        $stmt = $db->prepare("SELECT * FROM leave_records WHERE leave_id = ?");
        $stmt->execute([$leaveId]);
        $newData = $stmt->fetch(PDO::FETCH_ASSOC);

        Logger::audit('Leave Management', 'Update Leave ID: ' . $leaveId, $prevData, $newData);
    }

    // Delete a leave record
    public static function deleteLeave($leaveId) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM leave_records WHERE leave_id = ?");
        $stmt->execute([$leaveId]);
        $prevData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prevData) {
            throw new Exception("Leave record not found.");
        }

        // Validate SNCO location compliance
        LocationMiddleware::validatePersonnel($prevData['service_number']);

        $stmt = $db->prepare("DELETE FROM leave_records WHERE leave_id = :leave_id");
        $stmt->execute([':leave_id' => $leaveId]);

        Logger::audit('Leave Management', 'Delete Leave ID: ' . $leaveId, $prevData, null);
    }
}

