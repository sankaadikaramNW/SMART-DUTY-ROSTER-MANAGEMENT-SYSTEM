<?php
/**
 * Personnel Posting Transfers Workflow Controller
 */

class TransferController {

    // List all outgoing, incoming, or global transfers
    public function index() {
        $roleName = Session::get('role_name');
        $campId = Session::get('camp_id');

        $outgoingTransfers = [];
        $incomingTransfers = [];
        $allTransfers = [];

        if ($roleName === 'Administrator') {
            $allTransfers = Transfer::getAllTransfers();
        } else {
            $outgoingTransfers = Transfer::getOutgoing($campId);
            $incomingTransfers = Transfer::getIncoming($campId);
        }

        // Get camps for the transfer request modal
        $camps = Camp::getAll(true);

        $pageTitle = 'Personnel Posting Transfers';
        include __DIR__ . '/../views/postings/transfers_list.php';
    }

    // View specific transfer details and actions
    public function view() {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                throw new Exception("Transfer ID is required.");
            }

            $transfer = Transfer::getById($id);
            if (!$transfer) {
                throw new Exception("Transfer request not found.");
            }

            $roleName = Session::get('role_name');
            $userCampId = (int)Session::get('camp_id');
            $userId = Session::get('user_id');

            // Enforce location security for SNCO and OCPROVST
            if ($roleName !== 'Administrator') {
                if ((int)$transfer['from_camp_id'] !== $userCampId && (int)$transfer['to_camp_id'] !== $userCampId) {
                    throw new Exception("Security Error: Access Denied. You do not belong to the origin or destination base of this transfer.");
                }
            }

            // Auto-transition from 'Origin Approved' to 'Pending Destination Review' when Destination SNCO opens the transfer
            if (($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') && $userCampId === (int)$transfer['to_camp_id'] && $transfer['status'] === 'Origin Approved') {
                Transfer::transition($id, $userId, 'Destination SNCO', 'Approve', 'Opened request for review.');
                // Reload transfer to get updated status
                $transfer = Transfer::getById($id);
            }

            $approvals = Transfer::getApprovals($id);
            $camps = Camp::getAll(true);

            $pageTitle = "Transfer Details: " . htmlspecialchars($transfer['rank'] . ' ' . $transfer['full_name']);
            include __DIR__ . '/../views/postings/view_transfer.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/transfers');
        }
    }

    // Create a new transfer request
    public function create() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'SNCO' && $roleName !== 'Warrant Officer IC' && $roleName !== 'Administrator') {
                throw new Exception("Unauthorized Access: Only SNCO, Warrant Officer IC, or Administrator can create transfers.");
            }

            $serviceNumber = Security::sanitize($_POST['service_number'] ?? '');
            $toCampId = (int)($_POST['to_camp_id'] ?? 0);
            $effectiveDate = Security::sanitize($_POST['effective_date'] ?? '');
            $reason = Security::sanitize($_POST['reason'] ?? '');
            $remarks = Security::sanitize($_POST['remarks'] ?? '');
            
            // Get personnel info to find their camp
            $person = Personnel::getByServiceNumber($serviceNumber);
            if (!$person) {
                throw new Exception("Personnel profile not found.");
            }
            $fromCampId = (int)$person['camp_id'];

            // Validate that destination camp is different from origin
            if ($fromCampId === $toCampId) {
                throw new Exception("Transfer destination cannot be the same as the origin camp.");
            }

            // For SNCO or Warrant Officer IC, validate they can only transfer personnel from their own camp
            if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') {
                $userCampId = (int)Session::get('camp_id');
                if ($fromCampId !== $userCampId) {
                    throw new Exception("Security Error: You can only transfer personnel currently stationed in your camp.");
                }
            }

            // Handle file upload
            $supportingDocPath = null;
            if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../' . UPLOAD_PATH;
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['supporting_document']['name']);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['supporting_document']['tmp_name'], $targetFile)) {
                    $supportingDocPath = UPLOAD_PATH . $fileName;
                } else {
                    throw new Exception("Failed to upload supporting document.");
                }
            }

            // Add draft request
            $transferId = Transfer::createDraft($serviceNumber, $fromCampId, $toCampId, $effectiveDate, $reason, $remarks, $supportingDocPath, Session::get('user_id'));

            // Check if user clicked "Submit" immediately vs "Save as Draft"
            $actionSubmit = $_POST['submit_action'] ?? 'save_draft';
            if ($actionSubmit === 'submit_request') {
                // Transition to Pending Origin Approval
                $actionRole = ($roleName === 'Administrator') ? 'Administrator' : 'Origin SNCO';
                Transfer::transition($transferId, Session::get('user_id'), $actionRole, 'Submit', 'Submitted transfer request for approval.');
                Session::set('success_message', "Transfer request for $serviceNumber submitted for approval.");
            } else {
                Session::set('success_message', "Transfer draft for $serviceNumber created successfully.");
            }

            Response::redirect('/transfers');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/transfers');
        }
    }

    // Edit transfer request (Draft or Returned)
    public function edit() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'SNCO' && $roleName !== 'Warrant Officer IC' && $roleName !== 'Administrator') {
                throw new Exception("Unauthorized Access: Only SNCO, Warrant Officer IC, or Administrator can edit transfers.");
            }

            $transferId = (int)($_POST['transfer_id'] ?? 0);
            $transfer = Transfer::getById($transferId);
            if (!$transfer) {
                throw new Exception("Transfer request not found.");
            }

            if ($transfer['status'] !== 'Draft' && $transfer['status'] !== 'Returned for Correction') {
                throw new Exception("Only Draft or Returned requests can be edited.");
            }

            // Enforce camp isolation
            if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') {
                $userCampId = (int)Session::get('camp_id');
                if ((int)$transfer['from_camp_id'] !== $userCampId) {
                    throw new Exception("Security Error: You can only edit transfers for your own camp.");
                }
            }

            $toCampId = (int)($_POST['to_camp_id'] ?? 0);
            $effectiveDate = Security::sanitize($_POST['effective_date'] ?? '');
            $reason = Security::sanitize($_POST['reason'] ?? '');
            $remarks = Security::sanitize($_POST['remarks'] ?? '');

            if ($transfer['from_camp_id'] === $toCampId) {
                throw new Exception("Destination camp must be different from the origin camp.");
            }

            // Handle file upload
            $supportingDocPath = null;
            if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../' . UPLOAD_PATH;
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['supporting_document']['name']);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['supporting_document']['tmp_name'], $targetFile)) {
                    $supportingDocPath = UPLOAD_PATH . $fileName;
                } else {
                    throw new Exception("Failed to upload supporting document.");
                }
            }

            // Update request
            Transfer::updateDraft($transferId, $toCampId, $effectiveDate, $reason, $remarks, $supportingDocPath);

            // Check if user clicked "Submit" immediately vs "Save as Draft"
            $actionSubmit = $_POST['submit_action'] ?? 'save_draft';
            if ($actionSubmit === 'submit_request') {
                // Transition to Pending Origin Approval
                $actionRole = ($roleName === 'Administrator') ? 'Administrator' : 'Origin SNCO';
                Transfer::transition($transferId, Session::get('user_id'), $actionRole, 'Submit', 'Submitted transfer request for approval.');
                Session::set('success_message', "Transfer request for " . $transfer['service_number'] . " submitted for approval.");
            } else {
                Session::set('success_message', "Transfer request updated successfully.");
            }

            Response::redirect('/transfers');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/transfers');
        }
    }

    // Process workflow actions
    public function action() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            $userId = Session::get('user_id');
            $userCampId = (int)Session::get('camp_id');

            $transferId = (int)($_POST['transfer_id'] ?? 0);
            $action = Security::sanitize($_POST['action'] ?? '');
            $remarks = Security::sanitize($_POST['remarks'] ?? '');

            $transfer = Transfer::getById($transferId);
            if (!$transfer) {
                throw new Exception("Transfer request not found.");
            }

            // Enforce role actions mapping
            $actionRole = '';
            if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') {
                if ((int)$transfer['from_camp_id'] === $userCampId && in_array($action, ['Submit', 'Cancel'])) {
                    $actionRole = 'Origin SNCO';
                } elseif ((int)$transfer['to_camp_id'] === $userCampId && in_array($action, ['Submit'])) {
                    $actionRole = 'Destination SNCO';
                } else {
                    throw new Exception("Security Error: SNCO cannot perform this action for other camps.");
                }
            } elseif ($roleName === 'OCPROVST') {
                if ((int)$transfer['from_camp_id'] === $userCampId && $transfer['status'] === 'Pending Origin Approval') {
                    $actionRole = 'Origin OCPROVST';
                } elseif ((int)$transfer['to_camp_id'] === $userCampId && $transfer['status'] === 'Pending Destination Approval') {
                    $actionRole = 'Destination OCPROVST';
                } else {
                    throw new Exception("Security Error: OCPROVST cannot perform this action for other camps/states.");
                }
            } elseif ($roleName === 'Administrator') {
                $actionRole = 'Administrator';
            } else {
                throw new Exception("Security Error: You are not authorized to perform workflow actions.");
            }

            Transfer::transition($transferId, $userId, $actionRole, $action, $remarks);

            Session::set('success_message', "Workflow action successfully processed.");
            Response::redirect('/transfers/view?id=' . $transferId);
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/transfers');
        }
    }

    // Cancel transfer request
    public function cancel() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            $userId = Session::get('user_id');
            $userCampId = (int)Session::get('camp_id');

            $transferId = (int)($_POST['transfer_id'] ?? 0);
            $remarks = Security::sanitize($_POST['remarks'] ?? 'Request cancelled by creator.');

            $transfer = Transfer::getById($transferId);
            if (!$transfer) {
                throw new Exception("Transfer request not found.");
            }

            // Enforce SNCO camp check
            if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') {
                if ((int)$transfer['from_camp_id'] !== $userCampId) {
                    throw new Exception("Security Error: You can only cancel transfers originating from your own camp.");
                }
                $actionRole = 'Origin SNCO';
            } elseif ($roleName === 'Administrator') {
                $actionRole = 'Administrator';
            } else {
                throw new Exception("Security Error: Only the creator (SNCO) or Administrator can cancel a transfer.");
            }

            Transfer::transition($transferId, $userId, $actionRole, 'Cancel', $remarks);

            Session::set('success_message', "Transfer request cancelled.");
            Response::redirect('/transfers');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/transfers');
        }
    }
}
