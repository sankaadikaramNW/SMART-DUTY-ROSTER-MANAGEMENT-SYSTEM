<?php
/**
 * Role Model
 */

class Role {

    // Retrieve all roles
    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM roles ORDER BY role_id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
