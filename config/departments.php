<?php
/**
 * Centralized department options/validation for event audience targeting.
 */

if (!function_exists('eventify_allowed_departments')) {
    function eventify_allowed_departments(): array
    {
        return [
            'ALL',
            'BSIT',
            'BSHM',
            'CONAHS',
            'Senior High',
            'High school department',
            'College of Communication, Information and Technology',
            'College of Accountancy and Business',
            'School of Law and Political Science',
            'College of Education',
            'College of Nursing and Allied health sciences',
            'College of Hospitality Management',
        ];
    }
}

if (!function_exists('eventify_normalize_department')) {
    function eventify_normalize_department(?string $department): string
    {
        $d = trim((string) $department);
        $allowed = eventify_allowed_departments();
        return in_array($d, $allowed, true) ? $d : 'ALL';
    }
}
