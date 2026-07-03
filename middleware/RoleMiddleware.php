<?php
/**
 * Role-Based Access Control (RBAC) Middleware
 */

class RoleMiddleware {

    // Evaluate route permission for the active user role
    public static function check($route) {
        $roleName = Session::get('role_name');
        
        // Define role permissions matrix
        $rolePermissions = [
            'Administrator' => [
                '/dashboard', '/logout', 
                '/personnel', '/personnel/view', '/personnel/search', '/personnel/history', '/personnel/add', '/personnel/edit', '/postings', '/postings/add',
                '/transfers', '/transfers/view', '/transfers/create', '/transfers/edit', '/transfers/action', '/transfers/cancel',
                '/rosters', '/rosters/create', '/rosters/save', '/rosters/view', '/rosters/approve', '/rosters/assignment-action', '/rosters/calendar', '/rosters/calendar-data', '/rosters/timeline', '/rosters/conflict-check', '/rosters/action',
                '/camps', '/camps/save', '/shifts', '/shifts/save', '/duty-types', '/duty-types/save', '/ranks', '/ranks/save', '/users', '/users/save', '/users/status',
                '/reports', '/reports/generate', '/audit-logs', '/notifications', '/notifications/read'
            ],
            'OCPROVST' => [
                '/dashboard', '/logout',
                '/personnel', '/personnel/view', '/personnel/search', '/personnel/history', '/postings',
                '/transfers', '/transfers/view', '/transfers/action',
                '/rosters', '/rosters/view', '/rosters/approve', '/rosters/assignment-action', '/rosters/calendar', '/rosters/calendar-data', '/rosters/timeline', '/rosters/action',
                '/users', '/users/save', '/users/status',
                '/reports', '/reports/generate', '/notifications', '/notifications/read'
            ],
            'Warrant Officer IC' => [
                '/dashboard', '/logout',
                '/personnel', '/personnel/view', '/personnel/search', '/personnel/history', '/personnel/add', '/personnel/edit', '/postings', '/postings/add',
                '/transfers', '/transfers/view', '/transfers/create', '/transfers/edit', '/transfers/action', '/transfers/cancel',
                '/rosters', '/rosters/create', '/rosters/save', '/rosters/view', '/rosters/calendar', '/rosters/calendar-data', '/rosters/timeline', '/rosters/conflict-check', '/rosters/action',
                '/users', '/users/save', '/users/status',
                '/reports', '/reports/generate', '/notifications', '/notifications/read'
            ],
            'SNCO' => [
                '/dashboard', '/logout',
                '/personnel', '/personnel/view', '/personnel/search', '/personnel/history', '/personnel/add', '/personnel/edit', '/postings', '/postings/add',
                '/transfers', '/transfers/view', '/transfers/create', '/transfers/edit', '/transfers/action', '/transfers/cancel',
                '/rosters', '/rosters/create', '/rosters/save', '/rosters/view', '/rosters/calendar', '/rosters/calendar-data', '/rosters/timeline', '/rosters/conflict-check', '/rosters/action',
                '/reports', '/reports/generate', '/notifications', '/notifications/read'
            ],
            'Airman' => [
                '/dashboard', '/logout',
                '/rosters/calendar-data', '/rosters/timeline',
                '/notifications', '/notifications/read'
            ]
        ];

        if (!isset($rolePermissions[$roleName]) || !in_array($route, $rolePermissions[$roleName])) {
            throw new Exception("Unauthorized Access: Your account ($roleName) does not have privileges for action: $route");
        }
    }
}
