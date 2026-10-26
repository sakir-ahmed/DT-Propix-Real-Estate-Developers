<?php

declare(strict_types=1);

return [
    'otp' => [
        'ttl_seconds' => 300,
        'max_send_per_hour' => 5,
        'max_verify_attempts' => 5,
    ],

    'login' => [
        'max_attempts_per_minute' => 5,
    ],

    'demo' => [
        'enabled' => filter_var(env('IS_DEMO', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'roles' => [
        'owner',
        'general-manager',
        'sales-manager',
        'sales-executive',
        'telecaller',
        'broker',
        'sales-accountant',
        'accountant',
        'project-manager',
        'hr-manager',
        'customer-support',
        'web-editor',
    ],

    'permissions' => [
        'dashboard.view',
        'leads.view',
        'leads.view-own',
        'leads.manage',
        'units.view',
        'units.view-price',
        'units.change-price',
        'bookings.view',
        'bookings.view-own',
        'bookings.view-discount',
        'bookings.discount',
        'bookings.cancel',
        'collections.view',
        'collections.record',
        'projects.view',
        'projects.view-cost',
        'projects.manage',
        'website.edit',
        'customers.view',
        'reports.view',
        'reports.export',
        'approvals.decide',
        'settings.manage',
        'access-log.view',
        'payroll.view',
        'payroll.approve',
        'commission.view',
        'journals.post',
        'tickets.manage',
        'documents.view',
        'hr.manage',
    ],

    'role_permissions' => [
        'owner' => ['*'],
        'general-manager' => [
            'dashboard.view', 'leads.view', 'leads.manage', 'units.view', 'units.view-price',
            'bookings.view', 'bookings.view-discount', 'bookings.discount', 'bookings.cancel',
            'collections.view', 'projects.view', 'reports.view', 'reports.export',
            'approvals.decide', 'customers.view', 'documents.view',
        ],
        'sales-manager' => [
            'dashboard.view', 'leads.view', 'leads.manage', 'units.view', 'units.view-price',
            'bookings.view', 'bookings.view-discount', 'bookings.discount', 'projects.view',
            'customers.view',
        ],
        'sales-executive' => [
            'leads.view-own', 'leads.manage', 'units.view', 'bookings.view-own', 'projects.view', 'customers.view',
        ],
        'telecaller' => [
            'leads.view-own', 'leads.manage',
        ],
        'broker' => [
            'leads.view-own', 'commission.view',
        ],
        'sales-accountant' => [
            'collections.view', 'collections.record', 'customers.view', 'bookings.view', 'units.view',
        ],
        'accountant' => [
            'dashboard.view', 'collections.view', 'collections.record', 'journals.post',
            'reports.view', 'reports.export', 'commission.view',
        ],
        'project-manager' => [
            'projects.view', 'projects.view-cost', 'projects.manage', 'units.view',
        ],
        'hr-manager' => [
            'hr.manage', 'payroll.view', 'payroll.approve',
        ],
        'customer-support' => [
            'tickets.manage', 'customers.view', 'documents.view',
        ],
        'web-editor' => [
            'website.edit', 'projects.view',
        ],
    ],

    'menus' => [
        [
            'key' => 'dashboard',
            'label' => 'nav.dashboard',
            'icon' => 'chart-line',
            'route' => 'admin.dashboard',
            'permissions' => ['dashboard.view'],
        ],
        [
            'key' => 'leads',
            'label' => 'nav.leads',
            'icon' => 'user-plus',
            'route' => 'admin.leads.index',
            'permissions' => ['leads.view', 'leads.view-own'],
        ],
        [
            'key' => 'projects',
            'label' => 'nav.projects',
            'icon' => 'building',
            'route' => 'admin.projects.index',
            'permissions' => ['projects.view', 'units.view'],
        ],
        [
            'key' => 'customers',
            'label' => 'nav.customers',
            'icon' => 'users',
            'route' => 'admin.customers.index',
            'permissions' => ['customers.view'],
        ],
        [
            'key' => 'bookings',
            'label' => 'nav.bookings',
            'icon' => 'file-signature',
            'route' => 'admin.bookings.index',
            'permissions' => ['bookings.view', 'bookings.view-own'],
        ],
        [
            'key' => 'collections',
            'label' => 'nav.collections',
            'icon' => 'cash-register',
            'route' => 'admin.collections.index',
            'permissions' => ['collections.view'],
        ],
        [
            'key' => 'settings',
            'label' => 'nav.settings',
            'icon' => 'gear',
            'route' => 'admin.dashboard',
            'permissions' => ['settings.manage'],
        ],
    ],
];
