<?php
/**
 * AuditLog Model
 */

class AuditLog {

    // Retrieve system audit logs with pagination support
    public static function getAll($module = null, $user = null, $limit = 100, $offset = 0) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT a.*, p.full_name, rk.rank_name AS `rank` 
                FROM audit_logs a 
                LEFT JOIN personnel p ON a.service_number = p.service_number 
                LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                WHERE 1=1";
        
        $params = [];
        if ($module) {
            $sql .= " AND a.module = :module";
            $params[':module'] = $module;
        }
        if ($user) {
            $sql .= " AND (a.service_number LIKE :user OR p.full_name LIKE :user)";
            $params[':user'] = '%' . $user . '%';
        }
        
        $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($sql);
        
        // Bind parameters correctly
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Count records for pagination calculations
    public static function getCount($module = null, $user = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as cnt 
                FROM audit_logs a 
                LEFT JOIN personnel p ON a.service_number = p.service_number 
                WHERE 1=1";
        
        $params = [];
        if ($module) {
            $sql .= " AND a.module = :module";
            $params[':module'] = $module;
        }
        if ($user) {
            $sql .= " AND (a.service_number LIKE :user OR p.full_name LIKE :user)";
            $params[':user'] = '%' . $user . '%';
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return $res ? (int)$res['cnt'] : 0;
    }

    // Safely decode JSON audit payloads
    public static function decodeJson($jsonData) {
        if (!$jsonData) return [];
        $data = json_decode($jsonData, true);
        return is_array($data) ? $data : [];
    }

    // Resolves raw IDs to human-readable names from the database
    private static function resolveValue($key, $val) {
        $lk = strtolower($key);
        if ($val === null || $val === '') return 'N/A';
        try {
            $db = Database::getInstance()->getConnection();
            if ($lk === 'rank_id') {
                $stmt = $db->prepare("SELECT rank_name FROM ranks WHERE rank_id = ?");
                $stmt->execute([$val]);
                $res = $stmt->fetchColumn();
                return $res ? $res : $val;
            }
            if ($lk === 'camp_id' || $lk === 'from_camp_id' || $lk === 'to_camp_id') {
                $stmt = $db->prepare("SELECT camp_name FROM camps WHERE camp_id = ?");
                $stmt->execute([$val]);
                $res = $stmt->fetchColumn();
                return $res ? $res : $val;
            }
            if ($lk === 'role_id') {
                $stmt = $db->prepare("SELECT role_name FROM roles WHERE role_id = ?");
                $stmt->execute([$val]);
                $res = $stmt->fetchColumn();
                return $res ? $res : $val;
            }
            if ($lk === 'duty_type_id') {
                $stmt = $db->prepare("SELECT duty_type_name FROM duty_types WHERE duty_type_id = ?");
                $stmt->execute([$val]);
                $res = $stmt->fetchColumn();
                return $res ? $res : $val;
            }
            if ($lk === 'shift_id') {
                $stmt = $db->prepare("SELECT shift_name FROM shifts WHERE shift_id = ?");
                $stmt->execute([$val]);
                $res = $stmt->fetchColumn();
                return $res ? $res : $val;
            }
        } catch (Exception $e) {
            // Fallback silently to raw value
        }
        return $val;
    }

    // Compare previous and new data, filtering out sensitive columns and metadata
    public static function getDiff($prevJson, $newJson) {
        $prev = self::decodeJson($prevJson);
        $new = self::decodeJson($newJson);

        $sensitive = [
            'password', 'password_hash', 'confirm_password', 'remember_token', 
            'token', 'hash', 'password_confirm', 'new_password', 'api_key', 'secret'
        ];
        $ignored = ['created_at', 'updated_at', 'id', 'updated_by'];

        $filter = function($arr) use ($sensitive, $ignored) {
            if (!is_array($arr)) return [];
            $res = [];
            foreach ($arr as $k => $v) {
                $lk = strtolower($k);
                if (in_array($lk, $sensitive) || in_array($lk, $ignored)) {
                    continue;
                }
                $res[$k] = $v;
            }
            return $res;
        };

        $prevFiltered = $filter($prev);
        $newFiltered = $filter($new);

        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($prevFiltered), array_keys($newFiltered)));

        foreach ($allKeys as $key) {
            $prevVal = $prevFiltered[$key] ?? null;
            $newVal = $newFiltered[$key] ?? null;

            $prevValStr = is_array($prevVal) ? json_encode($prevVal) : (string)$prevVal;
            $newValStr = is_array($newVal) ? json_encode($newVal) : (string)$newVal;

            if ($prevValStr !== $newValStr) {
                $formatVal = function($k, $val) {
                    if ($val === null) return 'N/A';
                    if ($val === true || $val === '1' || $val === 1 || $val === 'Active') return 'Active';
                    if ($val === false || $val === '0' || $val === 0 || $val === 'Inactive') return 'Inactive';
                    return (string)self::resolveValue($k, $val);
                };

                $label = ucwords(str_replace('_', ' ', $key));
                if (substr($label, -3) === ' Id') {
                    $label = substr($label, 0, -3);
                }

                $changes[$label] = [
                    'prev' => $formatVal($key, $prevVal),
                    'new' => $formatVal($key, $newVal)
                ];
            }
        }
        return $changes;
    }

    // Parse entity details and format properties for user view
    public static function parseEntityDetails($log) {
        $module = $log['module'] ?? '';
        $action = $log['action'] ?? '';
        $prevData = self::decodeJson($log['previous_data']);
        $newData = self::decodeJson($log['new_data']);

        $entityType = 'Other';
        $entityId = 'N/A';

        // Identify Entity from Action or Module
        if (strpos($action, 'Personnel:') !== false) {
            $entityType = 'Personnel';
            $parts = explode(':', $action);
            $entityId = isset($parts[1]) ? trim($parts[1]) : 'N/A';
        } elseif ($module === 'Personnel Management') {
            $entityType = 'Personnel';
            $entityId = $newData['service_number'] ?? $prevData['service_number'] ?? 'N/A';
        } elseif (strpos($action, 'User ID:') !== false || $module === 'User Management') {
            $entityType = 'User Account';
            $entityId = $newData['service_number'] ?? $prevData['service_number'] ?? 'N/A';
            if ($entityId === 'N/A' && preg_match('/ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'ID #' . $m[1];
            }
        } elseif (strpos($action, 'Roster ID:') !== false || $module === 'Roster Management') {
            $entityType = 'Roster / Duty Assignment';
            $entityId = $newData['roster_name'] ?? $prevData['roster_name'] ?? 'N/A';
            if ($entityId === 'N/A' && preg_match('/ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'ID #' . $m[1];
            }
        } elseif ($module === 'Roster Approval') {
            $entityType = 'Roster Approval';
            if (preg_match('/Roster ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'Roster ID #' . $m[1];
            }
        } elseif (strpos($action, 'Camp ID:') !== false || $module === 'Camp Management') {
            $entityType = 'Camp / Base';
            $entityId = $newData['camp_name'] ?? $prevData['camp_name'] ?? 'N/A';
            if ($entityId === 'N/A' && preg_match('/ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'ID #' . $m[1];
            }
        } elseif (strpos($action, 'Shift ID:') !== false || $module === 'Shift Management') {
            $entityType = 'Shift';
            $entityId = $newData['shift_name'] ?? $prevData['shift_name'] ?? 'N/A';
            if ($entityId === 'N/A' && preg_match('/ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'ID #' . $m[1];
            }
        } elseif (strpos($action, 'Rank ID:') !== false || $module === 'Rank Management') {
            $entityType = 'Rank';
            $entityId = $newData['rank_name'] ?? $prevData['rank_name'] ?? 'N/A';
            if ($entityId === 'N/A' && preg_match('/ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'ID #' . $m[1];
            }
        } elseif (strpos($action, 'Duty Type ID:') !== false || $module === 'Duty Type Management') {
            $entityType = 'Duty Type';
            $entityId = $newData['duty_type_name'] ?? $prevData['duty_type_name'] ?? 'N/A';
            if ($entityId === 'N/A' && preg_match('/ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'ID #' . $m[1];
            }
        } elseif ($module === 'Posting Transfer' || $module === 'Posting Management') {
            $entityType = 'Station Transfer';
            $entityId = $newData['service_number'] ?? $prevData['service_number'] ?? 'N/A';
            if ($entityId === 'N/A' && preg_match('/ID:\s*(\d+)/i', $action, $m)) {
                $entityId = 'Transfer ID #' . $m[1];
            }
        } elseif ($module === 'Authentication') {
            $entityType = 'Authentication Event';
            $parts = explode(':', $action);
            $entityId = isset($parts[1]) ? trim($parts[1]) : 'N/A';
        }

        // Determine Action Type (CREATE, UPDATE, DELETE, AUTH)
        $actionType = 'UPDATE';
        $lAction = strtolower($action);
        if (strpos($lAction, 'create') !== false || strpos($lAction, 'saved roster') !== false || strpos($lAction, 'add') !== false) {
            $actionType = 'CREATE';
        } elseif (strpos($lAction, 'delete') !== false || strpos($lAction, 'remove') !== false) {
            $actionType = 'DELETE';
        } elseif (strpos($lAction, 'login') !== false || strpos($lAction, 'logout') !== false) {
            $actionType = 'AUTH';
        }

        $details = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action_type' => $actionType,
            'data' => []
        ];

        if ($actionType === 'CREATE') {
            $fields = [];
            foreach ($newData as $k => $v) {
                $lk = strtolower($k);
                if ($v === null || $v === '' || in_array($lk, ['password', 'password_hash', 'confirm_password', 'created_at', 'updated_at', 'id'])) {
                    continue;
                }
                $label = ucwords(str_replace('_', ' ', $k));
                if (substr($label, -3) === ' Id') {
                    $label = substr($label, 0, -3);
                }
                $fields[$label] = self::resolveValue($k, $v);
            }
            $details['data'] = $fields;
        } elseif ($actionType === 'DELETE') {
            $fields = [];
            foreach ($prevData as $k => $v) {
                $lk = strtolower($k);
                if ($v === null || $v === '' || in_array($lk, ['password', 'password_hash', 'confirm_password', 'created_at', 'updated_at', 'id'])) {
                    continue;
                }
                $label = ucwords(str_replace('_', ' ', $k));
                if (substr($label, -3) === ' Id') {
                    $label = substr($label, 0, -3);
                }
                $fields[$label] = self::resolveValue($k, $v);
            }
            $details['data'] = $fields;
        } elseif ($actionType === 'AUTH') {
            $result = (strpos($lAction, 'login') !== false) ? 'Success' : 'Logout';
            $browser = 'Unknown';
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $ua = $_SERVER['HTTP_USER_AGENT'];
                if (preg_match('/(Firefox|Chrome|Safari|Opera|MSIE|Edge)/i', $ua, $matches)) {
                    $browser = $matches[1];
                }
            }
            $details['data'] = [
                'Username' => $entityId,
                'Authentication Action' => strpos($lAction, 'login') !== false ? 'User Login' : 'User Logout',
                'Status / Result' => $result,
                'Browser / OS' => $browser
            ];
        }

        return $details;
    }
}
