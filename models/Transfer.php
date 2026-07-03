<?php
/**
 * Transfer Model
 * Handles the multi-level posting transfer workflow states and database transactions.
 */

class Transfer {

    // Retrieve single transfer by ID
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT pt.*, 
                   p.initials, p.full_name, rk.rank_name AS rank, rk.rank_short_name,
                   fc.camp_name AS from_camp_name, tc.camp_name AS to_camp_name,
                   u.service_number AS creator_service_number
            FROM posting_transfers pt
            JOIN personnel p ON pt.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            JOIN camps fc ON pt.from_camp_id = fc.camp_id
            JOIN camps tc ON pt.to_camp_id = tc.camp_id
            JOIN users u ON pt.created_by = u.user_id
            WHERE pt.transfer_id = :transfer_id
        ");
        $stmt->execute([':transfer_id' => $id]);
        return $stmt->fetch();
    }

    // Retrieve approvals history for a transfer
    public static function getApprovals($transferId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT pa.*, p.initials, p.full_name, rk.rank_short_name, u.service_number
            FROM posting_approvals pa
            JOIN users u ON pa.action_by = u.user_id
            JOIN personnel p ON u.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            WHERE pa.transfer_id = :transfer_id
            ORDER BY pa.created_at ASC
        ");
        $stmt->execute([':transfer_id' => $transferId]);
        return $stmt->fetchAll();
    }

    // Create a new transfer request in Draft status
    public static function createDraft($serviceNumber, $fromCampId, $toCampId, $effectiveDate, $reason, $remarks, $supportingDocument, $createdBy) {
        $db = Database::getInstance()->getConnection();
        
        // Validate camp isolation
        LocationMiddleware::validateCamp($fromCampId);
        LocationMiddleware::validatePersonnel($serviceNumber);

        $stmt = $db->prepare("
            INSERT INTO posting_transfers 
            (service_number, from_camp_id, to_camp_id, effective_date, reason, remarks, supporting_documents, status, created_by)
            VALUES 
            (:service_number, :from_camp_id, :to_camp_id, :effective_date, :reason, :remarks, :supporting_documents, 'Draft', :created_by)
        ");
        $stmt->execute([
            ':service_number' => $serviceNumber,
            ':from_camp_id' => $fromCampId,
            ':to_camp_id' => $toCampId,
            ':effective_date' => $effectiveDate,
            ':reason' => $reason,
            ':remarks' => $remarks,
            ':supporting_documents' => $supportingDocument,
            ':created_by' => $createdBy
        ]);

        $newId = $db->lastInsertId();
        Logger::audit('Posting Transfer', 'Create Transfer Draft ID: ' . $newId, null, [
            'service_number' => $serviceNumber,
            'from_camp_id' => $fromCampId,
            'to_camp_id' => $toCampId,
            'effective_date' => $effectiveDate,
            'status' => 'Draft'
        ]);

        // Add initial approval record
        self::addApprovalRecord($newId, $createdBy, 'Origin SNCO', 'Submit', 'Created transfer draft.');

        return $newId;
    }

    // Update a draft/returned transfer request
    public static function updateDraft($id, $toCampId, $effectiveDate, $reason, $remarks, $supportingDocument) {
        $db = Database::getInstance()->getConnection();
        $prevData = self::getById($id);
        if (!$prevData) {
            throw new Exception("Transfer request not found.");
        }

        // Validate camp access
        LocationMiddleware::validateCamp($prevData['from_camp_id']);
        
        $sql = "UPDATE posting_transfers 
                SET to_camp_id = :to_camp_id, effective_date = :effective_date, reason = :reason, remarks = :remarks";
        
        $params = [
            ':to_camp_id' => $toCampId,
            ':effective_date' => $effectiveDate,
            ':reason' => $reason,
            ':remarks' => $remarks,
            ':transfer_id' => $id
        ];

        if ($supportingDocument !== null) {
            $sql .= ", supporting_documents = :supporting_document";
            $params[':supporting_document'] = $supportingDocument;
        }

        $sql .= " WHERE transfer_id = :transfer_id";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $newData = self::getById($id);
        Logger::audit('Posting Transfer', 'Update Transfer Draft ID: ' . $id, $prevData, $newData);
    }

    // Add record to posting_approvals table helper
    private static function addApprovalRecord($transferId, $actionBy, $actionRole, $action, $remarks) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO posting_approvals (transfer_id, action_by, action_role, action, remarks)
            VALUES (:transfer_id, :action_by, :action_role, :action, :remarks)
        ");
        $stmt->execute([
            ':transfer_id' => $transferId,
            ':action_by' => $actionBy,
            ':action_role' => $actionRole,
            ':action' => $action,
            ':remarks' => $remarks
        ]);
    }

    // Execute state transition update
    public static function transition($id, $userId, $actionRole, $action, $remarks) {
        $db = Database::getInstance()->getConnection();
        $transfer = self::getById($id);
        if (!$transfer) {
            throw new Exception("Transfer request not found.");
        }

        $oldStatus = $transfer['status'];
        $newStatus = $oldStatus;

        // Perform authorization based on role and action
        if ($actionRole === 'Origin SNCO') {
            if ($action === 'Submit') {
                if ($oldStatus !== 'Draft' && $oldStatus !== 'Returned for Correction') {
                    throw new Exception("Only Draft or Returned requests can be submitted.");
                }
                LocationMiddleware::validateCamp($transfer['from_camp_id']);
                $newStatus = 'Pending Origin Approval';
            } elseif ($action === 'Cancel') {
                if ($oldStatus !== 'Draft' && $oldStatus !== 'Returned for Correction' && $oldStatus !== 'Pending Origin Approval') {
                    throw new Exception("Only Draft, Returned, or Pending Origin requests can be cancelled.");
                }
                LocationMiddleware::validateCamp($transfer['from_camp_id']);
                $newStatus = 'Cancelled';
            }
        } elseif ($actionRole === 'Origin OCPROVST') {
            LocationMiddleware::validateCamp($transfer['from_camp_id']);
            if ($oldStatus !== 'Pending Origin Approval') {
                throw new Exception("Request is not pending Origin approval.");
            }
            if ($action === 'Approve') {
                $newStatus = 'Origin Approved';
            } elseif ($action === 'Reject') {
                $newStatus = 'Rejected';
            } elseif ($action === 'Return') {
                $newStatus = 'Returned for Correction';
            }
        } elseif ($actionRole === 'Destination SNCO') {
            LocationMiddleware::validateCamp($transfer['to_camp_id']);
            // Allow processing either if status is 'Origin Approved' or if SNCO views it, change to 'Pending Destination Review' first
            if ($oldStatus !== 'Origin Approved' && $oldStatus !== 'Pending Destination Review') {
                throw new Exception("Request is not approved by origin.");
            }
            if ($action === 'Submit') {
                $newStatus = 'Pending Destination Approval';
            } elseif ($action === 'Approve') {
                // If they just view and it transitions to review status
                $newStatus = 'Pending Destination Review';
            }
        } elseif ($actionRole === 'Destination OCPROVST') {
            LocationMiddleware::validateCamp($transfer['to_camp_id']);
            if ($oldStatus !== 'Pending Destination Approval') {
                throw new Exception("Request is not pending Destination approval.");
            }
            if ($action === 'Approve') {
                $newStatus = 'Transfer Completed';
            } elseif ($action === 'Reject') {
                $newStatus = 'Rejected';
            } elseif ($action === 'Return') {
                $newStatus = 'Returned for Correction';
            }
        } elseif ($actionRole === 'Administrator') {
            // Administrative Override
            if ($action === 'Override') {
                $newStatus = 'Transfer Completed';
            } elseif ($action === 'Cancel') {
                $newStatus = 'Cancelled';
            }
        } else {
            throw new Exception("Invalid role for workflow action.");
        }

        // Handle transaction for completion
        if ($newStatus === 'Transfer Completed') {
            self::executeCompletion($id, $userId, $actionRole, $action, $remarks);
            return;
        }

        // Standard status update
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE posting_transfers SET status = :status WHERE transfer_id = :transfer_id");
            $stmt->execute([':status' => $newStatus, ':transfer_id' => $id]);

            self::addApprovalRecord($id, $userId, $actionRole, $action, $remarks);

            $db->commit();
            Logger::audit('Posting Transfer', "Transition Transfer ID $id to $newStatus", $transfer, ['status' => $newStatus, 'remarks' => $remarks]);

            // Notify users of state change
            self::sendNotifications($id, $oldStatus, $newStatus);

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // Atomic transaction for completing transfer
    private static function executeCompletion($transferId, $userId, $actionRole, $action, $remarks) {
        $db = Database::getInstance()->getConnection();
        $transfer = self::getById($transferId);
        if (!$transfer) {
            throw new Exception("Transfer not found.");
        }

        $serviceNumber = $transfer['service_number'];
        $fromCampId = $transfer['from_camp_id'];
        $toCampId = $transfer['to_camp_id'];
        $effectiveDate = $transfer['effective_date'];

        $db->beginTransaction();
        try {
            // 1. Update transfer request status
            $stmt = $db->prepare("UPDATE posting_transfers SET status = 'Transfer Completed' WHERE transfer_id = :transfer_id");
            $stmt->execute([':transfer_id' => $transferId]);

            // 2. Add approval history record
            self::addApprovalRecord($transferId, $userId, $actionRole, $action, $remarks);

            // 3. Close the previous active posting
            $stmt = $db->prepare("
                UPDATE postings 
                SET status = 'Completed', end_date = :end_date 
                WHERE service_number = :service_number AND status = 'Active'
            ");
            $stmt->execute([
                ':end_date' => $effectiveDate,
                ':service_number' => $serviceNumber
            ]);

            // 4. Create new active posting
            $stmt = $db->prepare("
                INSERT INTO postings (service_number, from_camp_id, to_camp_id, effective_date, end_date, status) 
                VALUES (:service_number, :from_camp_id, :to_camp_id, :effective_date, NULL, 'Active')
            ");
            $stmt->execute([
                ':service_number' => $serviceNumber,
                ':from_camp_id' => $fromCampId,
                ':to_camp_id' => $toCampId,
                ':effective_date' => $effectiveDate
            ]);

            // 5. Update personnel master record camp
            $stmt = $db->prepare("UPDATE personnel SET camp_id = :camp_id WHERE service_number = :service_number");
            $stmt->execute([
                ':camp_id' => $toCampId,
                ':service_number' => $serviceNumber
            ]);

            $db->commit();
            Logger::audit('Posting Transfer', "Completed Transfer ID $transferId", $transfer, ['status' => 'Transfer Completed']);

            // Send notification for completion
            self::sendNotifications($transferId, $transfer['status'], 'Transfer Completed');

        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception("Transaction Failed: " . $e->getMessage());
        }
    }

    // Retrieve outgoing transfers from a camp
    public static function getOutgoing($campId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT pt.*, p.initials, p.full_name, rk.rank_short_name, tc.camp_name AS to_camp_name, fc.camp_name AS from_camp_name
            FROM posting_transfers pt
            JOIN personnel p ON pt.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            JOIN camps tc ON pt.to_camp_id = tc.camp_id
            JOIN camps fc ON pt.from_camp_id = fc.camp_id
            WHERE pt.from_camp_id = :camp_id
            ORDER BY pt.updated_at DESC, pt.transfer_id DESC
        ");
        $stmt->execute([':camp_id' => $campId]);
        return $stmt->fetchAll();
    }

    // Retrieve incoming transfers to a camp (excluding Draft/Cancelled/Returned)
    public static function getIncoming($campId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT pt.*, p.initials, p.full_name, rk.rank_short_name, fc.camp_name AS from_camp_name, tc.camp_name AS to_camp_name
            FROM posting_transfers pt
            JOIN personnel p ON pt.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            JOIN camps fc ON pt.from_camp_id = fc.camp_id
            JOIN camps tc ON pt.to_camp_id = tc.camp_id
            WHERE pt.to_camp_id = :camp_id
              AND pt.status NOT IN ('Draft', 'Cancelled')
            ORDER BY pt.updated_at DESC, pt.transfer_id DESC
        ");
        $stmt->execute([':camp_id' => $campId]);
        return $stmt->fetchAll();
    }

    // Retrieve all transfers (Admin view)
    public static function getAllTransfers() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT pt.*, p.initials, p.full_name, rk.rank_short_name, fc.camp_name AS from_camp_name, tc.camp_name AS to_camp_name
            FROM posting_transfers pt
            JOIN personnel p ON pt.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            JOIN camps fc ON pt.from_camp_id = fc.camp_id
            JOIN camps tc ON pt.to_camp_id = tc.camp_id
            ORDER BY pt.updated_at DESC, pt.transfer_id DESC
        ");
        return $stmt->fetchAll();
    }

    // Helper to send notifications at different stages
    private static function sendNotifications($transferId, $oldStatus, $newStatus) {
        $transfer = self::getById($transferId);
        if (!$transfer) return;

        $name = $transfer['rank'] . ' ' . $transfer['initials'] . ' ' . $transfer['full_name'];
        $sn = $transfer['service_number'];
        $from = $transfer['from_camp_name'];
        $to = $transfer['to_camp_name'];

        if ($newStatus === 'Pending Origin Approval') {
            // Origin SNCO submits
            // Notify Origin SNCO (submitted)
            self::notifyUserByServiceNumber($transfer['created_by'], "Posting Request Submitted", "Transfer request for $name ($sn) to $to has been submitted.");
            // Notify Origin OCPROVST (requires approval)
            self::notifyRoleInCamp('OCPROVST', $transfer['from_camp_id'], "Transfer Approval Required", "Roster transfer for $name ($sn) to $to is awaiting your approval.");
        } elseif ($newStatus === 'Origin Approved') {
            // Origin OCPROVST approves
            // Notify Destination SNCO (incoming transfer received)
            self::notifyRoleInCamp('SNCO', $transfer['to_camp_id'], "Incoming Transfer Received", "Incoming transfer for $name ($sn) from $from has been received. Review required.");
        } elseif ($newStatus === 'Pending Destination Approval') {
            // Destination SNCO submits
            // Notify Destination OCPROVST (requires approval)
            self::notifyRoleInCamp('OCPROVST', $transfer['to_camp_id'], "Transfer Final Approval Required", "Incoming transfer for $name ($sn) from $from is awaiting your final approval.");
        } elseif ($newStatus === 'Transfer Completed') {
            // Destination OCPROVST approves
            // Notify Transferred Personnel
            Notification::add($sn, "Transfer Completed Successfully", "You have been successfully transferred from $from to $to effective on $transfer[effective_date].");
            // Notify Origin SNCO & Destination SNCO
            self::notifyRoleInCamp('SNCO', $transfer['from_camp_id'], "Transfer Completed", "Transfer of $name ($sn) to $to is completed.");
            self::notifyRoleInCamp('SNCO', $transfer['to_camp_id'], "Transfer Completed", "Transfer of $name ($sn) from $from is completed. Personnel added to strength.");
        } elseif ($newStatus === 'Returned for Correction') {
            // Returned by OCPROVST (either camp)
            // Notify Origin SNCO
            self::notifyUserByServiceNumber($transfer['created_by'], "Transfer Request Returned", "Transfer request for $name ($sn) to $to was returned for corrections.");
        } elseif ($newStatus === 'Rejected') {
            // Rejected
            // Notify Origin SNCO
            self::notifyUserByServiceNumber($transfer['created_by'], "Transfer Request Rejected", "Transfer request for $name ($sn) to $to has been rejected.");
        }
    }

    // Send notification to a role inside a camp
    private static function notifyRoleInCamp($roleName, $campId, $title, $message) {
        $db = Database::getInstance()->getConnection();
        $rolesToNotify = [$roleName];
        if ($roleName === 'SNCO') {
            $rolesToNotify[] = 'Warrant Officer IC';
        }
        
        $placeholders = implode(',', array_fill(0, count($rolesToNotify), '?'));
        
        $sql = "
            SELECT u.service_number 
            FROM users u
            JOIN personnel p ON u.service_number = p.service_number
            JOIN roles r ON u.role_id = r.role_id
            WHERE r.role_name IN ($placeholders) AND p.camp_id = ? AND u.status = 'Active' AND p.status = 'Active'
        ";
        
        $stmt = $db->prepare($sql);
        $params = array_merge($rolesToNotify, [$campId]);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $u) {
            Notification::add($u['service_number'], $title, $message);
        }
    }

    // Send notification to a specific user ID
    private static function notifyUserByServiceNumber($userId, $title, $message) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT service_number FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $serviceNumber = $stmt->fetchColumn();
        if ($serviceNumber) {
            Notification::add($serviceNumber, $title, $message);
        }
    }

    // Fetch dashboard transfer counts
    public static function getDashboardStats($campId = null) {
        $db = Database::getInstance()->getConnection();
        if ($campId !== null) {
            // Camp restricted stats
            // Outgoing stats
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_outgoing,
                    SUM(CASE WHEN status = 'Pending Origin Approval' THEN 1 ELSE 0 END) as pending_outgoing,
                    SUM(CASE WHEN status = 'Transfer Completed' THEN 1 ELSE 0 END) as completed_outgoing
                FROM posting_transfers
                WHERE from_camp_id = :camp_id
            ");
            $stmt->execute([':camp_id' => $campId]);
            $outgoing = $stmt->fetch();

            // Incoming stats
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_incoming,
                    SUM(CASE WHEN status IN ('Origin Approved', 'Pending Destination Review', 'Pending Destination Approval') THEN 1 ELSE 0 END) as pending_incoming,
                    SUM(CASE WHEN status = 'Transfer Completed' THEN 1 ELSE 0 END) as completed_incoming
                FROM posting_transfers
                WHERE to_camp_id = :camp_id
            ");
            $stmt->execute([':camp_id' => $campId]);
            $incoming = $stmt->fetch();

            return [
                'outgoing' => [
                    'total' => (int)($outgoing['total_outgoing'] ?? 0),
                    'pending' => (int)($outgoing['pending_outgoing'] ?? 0),
                    'completed' => (int)($outgoing['completed_outgoing'] ?? 0)
                ],
                'incoming' => [
                    'total' => (int)($incoming['total_incoming'] ?? 0),
                    'pending' => (int)($incoming['pending_incoming'] ?? 0),
                    'completed' => (int)($incoming['completed_incoming'] ?? 0)
                ]
            ];
        } else {
            // Global Admin stats
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status NOT IN ('Transfer Completed', 'Rejected', 'Cancelled') THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Transfer Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                FROM posting_transfers
            ");
            $res = $stmt->fetch();
            return [
                'total' => (int)($res['total'] ?? 0),
                'pending' => (int)($res['pending'] ?? 0),
                'completed' => (int)($res['completed'] ?? 0),
                'rejected' => (int)($res['rejected'] ?? 0)
            ];
        }
    }
}
