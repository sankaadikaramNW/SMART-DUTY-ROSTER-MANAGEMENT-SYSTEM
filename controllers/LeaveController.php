<?php
/**
 * Leave Controller
 */

class LeaveController {

    // Render Leaves Management Panel
    public function index() {
        try {
            $roleName = Session::get('role_name');
            $restrictedCampId = LocationMiddleware::getCampConstraint();
            
            // Subordinates to choose from in the form dropdown
            $subordinates = Personnel::getAll($restrictedCampId, 'Active');
            
            // List of leaves (optionally filtered by camp if constrained)
            $leaves = LeaveRecord::getAllWithPersonnel();
            if ($restrictedCampId !== null) {
                $leaves = array_filter($leaves, function($l) use ($restrictedCampId) {
                    return (int)$l['camp_id'] === $restrictedCampId;
                });
            }

            $pageTitle = 'Leave Management';
            include __DIR__ . '/../views/personnel/leaves.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/dashboard');
        }
    }

    // Process leave submission form
    public function save() {
        try {
            Security::verifyCsrf();
            
            $serviceNumber = Security::sanitize($_POST['service_number'] ?? '');
            $startDate = Security::sanitize($_POST['leave_start_date'] ?? '');
            $endDate = Security::sanitize($_POST['leave_end_date'] ?? '');
            $leaveType = Security::sanitize($_POST['leave_type'] ?? '');
            $approvedBy = Session::get('service_number');

            if (empty($serviceNumber) || empty($startDate) || empty($endDate) || empty($leaveType)) {
                throw new Exception("All fields are required.");
            }

            if (strtotime($startDate) > strtotime($endDate)) {
                throw new Exception("Leave start date cannot be after end date.");
            }

            // Verify if SNCO can manage this subordinate
            LocationMiddleware::validatePersonnel($serviceNumber);

            LeaveRecord::saveLeave($serviceNumber, $startDate, $endDate, $leaveType, $approvedBy);

            Session::set('success_message', "Leave record for $serviceNumber registered successfully.");
            Response::redirect('/leaves');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/leaves');
        }
    }

    // JSON API for interactive calendar
    public function calendarData() {
        try {
            $start = Security::sanitize($_GET['start'] ?? '');
            $end = Security::sanitize($_GET['end'] ?? '');

            if (empty($start) || empty($end)) {
                throw new Exception("Start and End dates are required.");
            }

            $leaves = LeaveRecord::getActiveLeavesRange($start, $end);
            return Response::json($leaves);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    // Report return from leave
    public function reportReturn() {
        try {
            Security::verifyCsrf();
            $leaveId = (int)($_POST['leave_id'] ?? 0);
            $reportingDate = Security::sanitize($_POST['actual_reporting_date'] ?? date('Y-m-d'));

            if (!$leaveId) {
                throw new Exception("Leave ID is required.");
            }

            LeaveRecord::reportReturn($leaveId, $reportingDate);

            Session::set('success_message', "Reporting date registered successfully.");
            Response::redirect('/leaves');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/leaves');
        }
    }

    // Grant additional leave extension
    public function grantExtension() {
        try {
            Security::verifyCsrf();
            $leaveId = (int)($_POST['leave_id'] ?? 0);
            $extendedDate = Security::sanitize($_POST['granted_end_date'] ?? '');
            $reason = Security::sanitize($_POST['granted_reason'] ?? '');
            $grantedBy = Session::get('service_number');

            if (!$leaveId || empty($extendedDate) || empty($reason)) {
                throw new Exception("Leave ID, Extended Date, and Reason are required.");
            }

            LeaveRecord::grantExtension($leaveId, $extendedDate, $reason, $grantedBy);

            Session::set('success_message', "Leave extension granted successfully.");
            Response::redirect('/leaves');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/leaves');
        }
    }
}
