<?php
/**
 * Location-based Isolation Middleware
 * Enforces the business rule that SNCOs can only see/interact with personnel and rosters of their own camp.
 */

class LocationMiddleware {

    // Retrieve active camp constraint. Returns camp_id for SNCO, null for Admin/OCPROVST (allowing global view)
    public static function getCampConstraint() {
        if (Session::get('role_name') === 'SNCO') {
            $campId = Session::get('camp_id');
            if (!$campId) {
                throw new Exception("Security Error: Camp mapping not found for SNCO.");
            }
            return (int)$campId;
        }
        return null;
    }

    // Verify if a specific camp action is allowed
    public static function validateCamp($campId) {
        $restrictedCampId = self::getCampConstraint();
        if ($restrictedCampId !== null && (int)$campId !== $restrictedCampId) {
            throw new Exception("Security Error: Access Denied. SNCO cannot access or assign rosters for other camps.");
        }
    }

    // Verify if a service number belongs to the SNCO's camp
    public static function validatePersonnel($serviceNumber) {
        $restrictedCampId = self::getCampConstraint();
        if ($restrictedCampId !== null) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT camp_id FROM personnel WHERE service_number = :service_number");
            $stmt->execute([':service_number' => $serviceNumber]);
            $person = $stmt->fetch();
            
            if (!$person || (int)$person['camp_id'] !== $restrictedCampId) {
                throw new Exception("Security Error: Access Denied. Personnel does not belong to your camp.");
            }
        }
    }
}
