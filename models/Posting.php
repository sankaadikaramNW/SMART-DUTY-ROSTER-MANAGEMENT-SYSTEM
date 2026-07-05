<?php
/**
 * Posting Model
 */

class Posting {

    // Retrieve posting records history
    public static function getHistory($serviceNumber = null) {
        $db = Database::getInstance()->getConnection();
        
        // Enforce location restrictions for SNCO
        $restrictedCampId = LocationMiddleware::getCampConstraint();
        
        $sql = "SELECT p.*, fc.camp_name as from_camp, tc.camp_name as to_camp, pers.initials, pers.full_name, rk.rank_name AS `rank` 
                FROM postings p 
                JOIN camps fc ON p.from_camp_id = fc.camp_id 
                JOIN camps tc ON p.to_camp_id = tc.camp_id 
                JOIN personnel pers ON p.service_number = pers.service_number 
                LEFT JOIN ranks rk ON pers.rank_id = rk.rank_id
                WHERE 1=1";
        
        $params = [];
        if ($serviceNumber !== null) {
            // Validate location permissions
            LocationMiddleware::validatePersonnel($serviceNumber);
            $sql .= " AND p.service_number = :service_number";
            $params[':service_number'] = $serviceNumber;
        } elseif ($restrictedCampId !== null) {
            // SNCO can only see posting history of personnel currently posted in their camp
            $sql .= " AND pers.camp_id = :camp_id";
            $params[':camp_id'] = $restrictedCampId;
        }

        $sql .= " ORDER BY p.effective_date DESC, p.posting_id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Add posting record, completing the old record and updating the personnel's active camp ID
    public static function addPostingRecord($serviceNumber, $fromCampId, $toCampId, $effectiveDate) {
        $db = Database::getInstance()->getConnection();
        
        // Validate location compliance
        LocationMiddleware::validateCamp($fromCampId);
        LocationMiddleware::validateCamp($toCampId);
        LocationMiddleware::validatePersonnel($serviceNumber);

        // 1. Terminate current active posting records
        $stmt = $db->prepare("
            UPDATE postings 
            SET status = 'Completed', end_date = :end_date 
            WHERE service_number = :service_number AND status = 'Active'
        ");
        $stmt->execute([
            ':end_date' => date('Y-m-d', strtotime($effectiveDate . ' -1 day')),
            ':service_number' => $serviceNumber
        ]);

        // 2. Insert new active posting record
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

        // 3. Move active camp designation in the personnel table
        $stmt = $db->prepare("
            UPDATE personnel 
            SET camp_id = :camp_id 
            WHERE service_number = :service_number
        ");
        $stmt->execute([
            ':camp_id' => $toCampId,
            ':service_number' => $serviceNumber
        ]);
        
        Logger::audit('Posting Management', 'Moved Personnel ' . $serviceNumber . ' from camp: ' . $fromCampId . ' to camp: ' . $toCampId . ' effective: ' . $effectiveDate);
    }
}
