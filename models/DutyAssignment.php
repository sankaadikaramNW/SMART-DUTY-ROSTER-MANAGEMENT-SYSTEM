<?php
/**
 * DutyAssignment Model
 */

class DutyAssignment {

    // Fetch assignments for a given roster
    public static function getByRosterId($rosterId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT a.*, rk.rank_name AS rank, p.initials, p.full_name, p.trade, p.squadron, p.status AS personnel_status, c.camp_name,
                   s.shift_name, s.start_time, s.end_time, t.duty_type_name,
                   pos.effective_date AS posting_effective_date,
                   pos_from.camp_name AS posting_from_camp_name
            FROM duty_assignments a
            JOIN personnel p ON a.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            JOIN camps c ON p.camp_id = c.camp_id
            JOIN shifts s ON a.shift_id = s.shift_id
            JOIN duty_types t ON a.duty_type_id = t.duty_type_id
            LEFT JOIN postings pos ON p.service_number = pos.service_number AND pos.status = 'Active'
            LEFT JOIN camps pos_from ON pos.from_camp_id = pos_from.camp_id
            WHERE a.roster_id = :roster_id
            ORDER BY a.duty_date ASC, s.start_time ASC
        ");
        $stmt->execute([':roster_id' => $rosterId]);
        return $stmt->fetchAll();
    }

    // Save multiple assignments for a roster (with transaction rollback)
    public static function saveMultiple($rosterId, $assignments) {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            // Delete existing assignments for the roster
            $stmt = $db->prepare("DELETE FROM duty_assignments WHERE roster_id = :roster_id");
            $stmt->execute([':roster_id' => $rosterId]);

            if (!empty($assignments)) {
                $stmt = $db->prepare("
                    INSERT INTO duty_assignments 
                    (roster_id, duty_date, duty_type_id, shift_id, service_number, priority_level, remarks, conflict_level, justification, supervisor_remarks)
                    VALUES 
                    (:roster_id, :duty_date, :duty_type_id, :shift_id, :service_number, :priority_level, :remarks, :conflict_level, :justification, :supervisor_remarks)
                ");
                foreach ($assignments as $a) {
                    $stmt->execute([
                        ':roster_id' => $rosterId,
                        ':duty_date' => $a['duty_date'],
                        ':duty_type_id' => $a['duty_type_id'],
                        ':shift_id' => $a['shift_id'],
                        ':service_number' => $a['service_number'],
                        ':priority_level' => isset($a['priority_level']) ? $a['priority_level'] : 'Low',
                        ':remarks' => isset($a['remarks']) ? $a['remarks'] : null,
                        ':conflict_level' => isset($a['conflict_level']) ? $a['conflict_level'] : 'Normal',
                        ':justification' => isset($a['justification']) ? $a['justification'] : null,
                        ':supervisor_remarks' => isset($a['supervisor_remarks']) ? $a['supervisor_remarks'] : null
                    ]);
                }
            }
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // Get assignments in a date range for calendar views
    public static function getCalendarData($campId, $startDate, $endDate, $statusList = []) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT a.*, rk.rank_name AS rank, p.initials, p.full_name, s.shift_name, s.start_time, s.end_time, t.duty_type_name, r.roster_name, r.status AS roster_status, c.camp_name
                FROM duty_assignments a
                JOIN duty_rosters r ON a.roster_id = r.roster_id
                JOIN camps c ON r.camp_id = c.camp_id
                JOIN personnel p ON a.service_number = p.service_number
                LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                JOIN shifts s ON a.shift_id = s.shift_id
                JOIN duty_types t ON a.duty_type_id = t.duty_type_id
                WHERE r.camp_id = :camp_id AND a.duty_date BETWEEN :start_date AND :end_date";
        
        $params = [
            ':camp_id' => $campId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if (!empty($statusList)) {
            $placeholders = [];
            foreach ($statusList as $i => $status) {
                $key = ":status_$i";
                $placeholders[] = $key;
                $params[$key] = $status;
            }
            $sql .= " AND r.status IN (" . implode(',', $placeholders) . ")";
        }

        $sql .= " ORDER BY a.duty_date ASC, s.start_time ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Analyze assignments for conflicts
    public static function checkConflicts($campId, $startDate, $endDate, $assignments, $excludeRosterId = null) {
        $db = Database::getInstance()->getConnection();
        
        if (empty($assignments)) {
            return [];
        }

        // 1. Fetch all shifts to identify durations and names
        $stmt = $db->query("SELECT * FROM shifts WHERE status = 'Active'");
        $shifts = [];
        foreach ($stmt->fetchAll() as $s) {
            $shifts[$s['shift_id']] = $s;
        }

        // 2. Extract unique service numbers from proposed assignments
        $serviceNumbers = [];
        foreach ($assignments as $a) {
            if (!empty($a['service_number'])) {
                $serviceNumbers[] = $a['service_number'];
            }
        }
        $serviceNumbers = array_values(array_unique($serviceNumbers));

        $personnelMap = [];
        if (!empty($serviceNumbers)) {
            // Fetch personnel status and camp details
            $placeholders = implode(',', array_fill(0, count($serviceNumbers), '?'));
            $stmt = $db->prepare("
                SELECT p.service_number, rk.rank_name AS rank, p.full_name, p.status, p.camp_id 
                FROM personnel p
                LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                WHERE p.service_number IN ($placeholders)
            ");
            $stmt->execute($serviceNumbers);
            foreach ($stmt->fetchAll() as $p) {
                $personnelMap[$p['service_number']] = $p;
            }
        }

        // 3. Fetch existing database assignments for these service numbers in date range [startDate - 1 day, endDate + 1 day]
        $dbAssignments = [];
        if (!empty($serviceNumbers)) {
            $prevDate = date('Y-m-d', strtotime($startDate . ' -1 day'));
            $nextDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
            
            $placeholders = implode(',', array_fill(0, count($serviceNumbers), '?'));
            $sql = "SELECT a.*, r.roster_name, r.status AS roster_status
                    FROM duty_assignments a
                    JOIN duty_rosters r ON a.roster_id = r.roster_id
                    WHERE a.service_number IN ($placeholders)
                      AND a.duty_date BETWEEN ? AND ?";
            
            $params = array_merge($serviceNumbers, [$prevDate, $nextDate]);
            if ($excludeRosterId !== null) {
                $sql .= " AND a.roster_id != ?";
                $params[] = $excludeRosterId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $dbAssignments = $stmt->fetchAll();
        }

        // 4. Merge proposed and database assignments
        $schedule = []; // service_number => date => list of assignments

        // Add database assignments
        foreach ($dbAssignments as $da) {
            $sNum = $da['service_number'];
            $date = $da['duty_date'];
            if (!isset($schedule[$sNum])) {
                $schedule[$sNum] = [];
            }
            if (!isset($schedule[$sNum][$date])) {
                $schedule[$sNum][$date] = [];
            }
            $schedule[$sNum][$date][] = [
                'source' => 'database',
                'roster_name' => $da['roster_name'],
                'roster_status' => $da['roster_status'],
                'shift_id' => $da['shift_id'],
                'duty_type_id' => $da['duty_type_id'],
                'duty_date' => $da['duty_date']
            ];
        }

        // Add proposed assignments
        foreach ($assignments as $idx => $pa) {
            $sNum = $pa['service_number'];
            $date = $pa['duty_date'];
            if (empty($sNum) || empty($date)) {
                continue;
            }
            if (!isset($schedule[$sNum])) {
                $schedule[$sNum] = [];
            }
            if (!isset($schedule[$sNum][$date])) {
                $schedule[$sNum][$date] = [];
            }
            $schedule[$sNum][$date][] = [
                'source' => 'proposed',
                'index' => $idx,
                'shift_id' => $pa['shift_id'],
                'duty_type_id' => $pa['duty_type_id'],
                'duty_date' => $pa['duty_date']
            ];
        }

        // 5. Evaluate conflicts for each proposed assignment
        $conflicts = [];

        foreach ($assignments as $idx => $pa) {
            $sNum = $pa['service_number'];
            $date = $pa['duty_date'];
            if (empty($sNum) || empty($date)) {
                continue;
            }

            $paConflicts = [];

            // Check Personnel constraints
            $person = isset($personnelMap[$sNum]) ? $personnelMap[$sNum] : null;
            if ($person) {
                if ($person['status'] === 'Inactive') {
                    $paConflicts[] = [
                        'type' => 'Inactive State',
                        'level' => 'Critical',
                        'message' => "Personnel {$person['rank']} {$person['full_name']} is Inactive."
                    ];
                } elseif ($person['status'] === 'Leave') {
                    $paConflicts[] = [
                        'type' => 'Leave Overlap',
                        'level' => 'Critical',
                        'message' => "Personnel {$person['rank']} {$person['full_name']} is currently on Leave."
                    ];
                } elseif ($person['status'] === 'Temporary Duty') {
                    $paConflicts[] = [
                        'type' => 'TDY Overlap',
                        'level' => 'Warning',
                        'message' => "Personnel {$person['rank']} {$person['full_name']} is on Temporary Duty."
                    ];
                }

                // Check location compatibility
                if ((int)$person['camp_id'] !== (int)$campId) {
                    $paConflicts[] = [
                        'type' => 'Location Overlap',
                        'level' => 'Warning',
                        'message' => "Personnel belongs to a different camp than Roster camp."
                    ];
                }
            } else {
                $paConflicts[] = [
                    'type' => 'Invalid Personnel',
                    'level' => 'Critical',
                    'message' => "Service number $sNum does not exist."
                ];
            }

            // Check Double Booking
            $sameDayAssignments = isset($schedule[$sNum][$date]) ? $schedule[$sNum][$date] : [];
            $doubleBookCount = 0;
            $details = [];
            foreach ($sameDayAssignments as $sda) {
                $doubleBookCount++;
                if ($sda['source'] === 'database') {
                    $details[] = "Roster '{$sda['roster_name']}' (Status: {$sda['roster_status']})";
                } else {
                    $details[] = "Proposed assignment";
                }
            }
            if ($doubleBookCount > 1) {
                $paConflicts[] = [
                    'type' => 'Double Booking',
                    'level' => 'Critical',
                    'message' => "Double Booked: Assigned to multiple duties on $date (" . implode(', ', $details) . ")."
                ];
            }

            // Check Rest Period (24-Hour Duty)
            $prevDateStr = date('Y-m-d', strtotime($date . ' -1 day'));
            $prevDayAssignments = isset($schedule[$sNum][$prevDateStr]) ? $schedule[$sNum][$prevDateStr] : [];
            foreach ($prevDayAssignments as $pda) {
                $pShift = isset($shifts[$pda['shift_id']]) ? $shifts[$pda['shift_id']] : null;
                if ($pShift && (float)$pShift['duration_hours'] >= 24) {
                    $srcName = $pda['source'] === 'database' ? "Roster '{$pda['roster_name']}'" : "Proposed assignment";
                    $paConflicts[] = [
                        'type' => 'Rest Period Violation (24-Hour)',
                        'level' => 'Critical',
                        'message' => "Rest Period Violation: Personnel worked 24-Hour Duty on $prevDateStr ($srcName) and requires 24 hours of rest."
                    ];
                }
            }

            // Check Rest Period (Night Shift to Morning Shift)
            $currShift = isset($shifts[$pa['shift_id']]) ? $shifts[$pa['shift_id']] : null;
            if ($currShift && stripos($currShift['shift_name'], 'Morning') !== false) {
                foreach ($prevDayAssignments as $pda) {
                    $pShift = isset($shifts[$pda['shift_id']]) ? $shifts[$pda['shift_id']] : null;
                    if ($pShift && stripos($pShift['shift_name'], 'Night') !== false) {
                        $srcName = $pda['source'] === 'database' ? "Roster '{$pda['roster_name']}'" : "Proposed assignment";
                        $paConflicts[] = [
                            'type' => 'Rest Period Violation (Night Shift)',
                            'level' => 'Warning',
                            'message' => "Night to Morning Conflict: Personnel worked Night Shift on $prevDateStr ($srcName) and cannot work Morning Shift on $date."
                        ];
                    }
                }
            }

            if (!empty($paConflicts)) {
                $conflicts[$idx] = $paConflicts;
            }
        }

        return $conflicts;
    }

    // Update assignment status
    public static function updateStatus($assignmentId, $status, $supervisorRemarks = null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE duty_assignments 
            SET status = :status, supervisor_remarks = :remarks 
            WHERE assignment_id = :id
        ");
        return $stmt->execute([
            ':status' => $status,
            ':remarks' => $supervisorRemarks,
            ':id' => $assignmentId
        ]);
    }
}
