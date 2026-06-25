<?php
/**
 * Report Controller
 */

class ReportController {

    // Renders the main reports filter interface
    public function index() {
        $camps = Camp::getAll(true);
        $shifts = Shift::getAll(true);
        $dutyTypes = DutyType::getAll(true);
        
        $activeCampId = LocationMiddleware::getCampConstraint() ?? Session::get('camp_id') ?? 1;

        $pageTitle = 'Duty Statistics & Reports';
        include __DIR__ . '/../views/reports/index.php';
    }

    // Process filters and render print view or output CSV stream
    public function generate() {
        try {
            $campId = isset($_GET['camp_id']) && $_GET['camp_id'] !== '' ? (int)$_GET['camp_id'] : null;
            
            // Enforce location isolate constraints
            $restrictedCampId = LocationMiddleware::getCampConstraint();
            if ($restrictedCampId !== null) {
                $campId = $restrictedCampId;
            }

            if (!$campId) {
                throw new Exception("Please specify a target Camp/Base for the report.");
            }

            $startDate = Security::sanitize($_GET['start_date'] ?? date('Y-m-01'));
            $endDate = Security::sanitize($_GET['end_date'] ?? date('Y-m-t'));
            $shiftId = isset($_GET['shift_id']) && $_GET['shift_id'] !== '' ? (int)$_GET['shift_id'] : null;
            $dutyTypeId = isset($_GET['duty_type_id']) && $_GET['duty_type_id'] !== '' ? (int)$_GET['duty_type_id'] : null;
            $serviceNumber = Security::sanitize($_GET['service_number'] ?? '');
            $exportType = Security::sanitize($_GET['export_type'] ?? 'print');

            $db = Database::getInstance()->getConnection();

            $sql = "SELECT a.*, p.rank, p.initials, p.full_name, p.trade, p.squadron, s.shift_name, s.start_time, s.end_time, t.duty_type_name, r.roster_name, c.camp_name
                    FROM duty_assignments a
                    JOIN duty_rosters r ON a.roster_id = r.roster_id
                    JOIN camps c ON r.camp_id = c.camp_id
                    JOIN personnel p ON a.service_number = p.service_number
                    JOIN shifts s ON a.shift_id = s.shift_id
                    JOIN duty_types t ON a.duty_type_id = t.duty_type_id
                    WHERE r.camp_id = :camp_id AND a.duty_date BETWEEN :start_date AND :end_date";

            $params = [
                ':camp_id' => $campId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];

            if ($shiftId) {
                $sql .= " AND a.shift_id = :shift_id";
                $params[':shift_id'] = $shiftId;
            }
            if ($dutyTypeId) {
                $sql .= " AND a.duty_type_id = :duty_type_id";
                $params[':duty_type_id'] = $dutyTypeId;
            }
            if (!empty($serviceNumber)) {
                $sql .= " AND a.service_number = :service_number";
                $params[':service_number'] = $serviceNumber;
            }

            $sql .= " ORDER BY a.duty_date ASC, s.start_time ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();

            $campInfo = Camp::getById($campId);

            if ($exportType === 'csv') {
                // Return CSV Stream
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=Duty_Roster_Report_' . date('Ymd_His') . '.csv');
                $output = fopen('php://output', 'w');

                // Header columns
                fputcsv($output, ['Duty Date', 'Roster Name', 'Camp/Base', 'Service Number', 'Rank', 'Name', 'Trade', 'Squadron', 'Duty Type', 'Shift', 'Timings', 'Conflict Level', 'Justification']);

                foreach ($results as $row) {
                    fputcsv($output, [
                        $row['duty_date'],
                        $row['roster_name'],
                        $row['camp_name'],
                        $row['service_number'],
                        $row['rank'],
                        $row['initials'] . ' ' . $row['full_name'],
                        $row['trade'],
                        $row['squadron'],
                        $row['duty_type_name'],
                        $row['shift_name'],
                        $row['start_time'] . ' - ' . $row['end_time'],
                        $row['conflict_level'],
                        $row['justification']
                    ]);
                }
                fclose($output);
                exit;
            } else {
                // Render Print Friendly Page
                $pageTitle = 'Print Duty Report';
                include __DIR__ . '/../views/reports/print.php';
            }
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/reports');
        }
    }
}
