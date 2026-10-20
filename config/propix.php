<?php

declare(strict_types=1);

return [
    'domain' => env('PROPIX_DOMAIN', 'propix.test'),

    'idle_timeout_minutes' => (int) env('PROPIX_IDLE_TIMEOUT_MINUTES', 30),

    'otp' => [
        'ttl_seconds' => 300,
        'max_send_per_hour' => 5,
        'max_verify_attempts' => 5,
    ],

    'login' => [
        'max_attempts_per_minute' => 5,
    ],

    'demo' => [
        'owner_password' => env('DEMO_OWNER_PASSWORD'),
        'enabled' => filter_var(env('IS_DEMO', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'platform' => [
        'name' => env('PLATFORM_ADMIN_NAME', 'Datatech Super Admin'),
        'username' => env('PLATFORM_ADMIN_USERNAME', 'superadmin'),
        'email' => env('PLATFORM_ADMIN_EMAIL', 'platform@datatech.test'),
        'mobile' => env('PLATFORM_ADMIN_MOBILE', '8801700000000'),
        'password' => env('PLATFORM_ADMIN_PASSWORD'),
    ],

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'monthly_price' => '5000.00',
            'limits' => [
                'projects' => 3,
                'units' => 300,
                'users' => 5,
                'storage_mb' => 1024,
            ],
            'modules' => [
                'website', 'crm', 'units', 'booking', 'collections', 'reports',
            ],
        ],
        'growth' => [
            'name' => 'Growth',
            'monthly_price' => '15000.00',
            'limits' => [
                'projects' => 10,
                'units' => 2000,
                'users' => 20,
                'storage_mb' => 10240,
            ],
            'modules' => [
                'website', 'crm', 'units', 'booking', 'collections', 'reports',
                'brokers', 'accounts', 'documents', 'messaging', 'portal',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'monthly_price' => '40000.00',
            'limits' => [
                'projects' => null,
                'units' => null,
                'users' => 60,
                'storage_mb' => 51200,
            ],
            'modules' => [
                'website', 'crm', 'units', 'booking', 'collections', 'reports',
                'brokers', 'accounts', 'documents', 'messaging', 'portal',
                'hr', 'costing', 'progress', 'whatsapp', 'custom_domain', 'api',
            ],
        ],
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
            'route' => 'admin.dashboard',
            'permissions' => ['leads.view', 'leads.view-own'],
        ],
        [
            'key' => 'units',
            'label' => 'nav.units',
            'icon' => 'door-open',
            'route' => 'admin.dashboard',
            'permissions' => ['units.view'],
        ],
        [
            'key' => 'bookings',
            'label' => 'nav.bookings',
            'icon' => 'file-signature',
            'route' => 'admin.dashboard',
            'permissions' => ['bookings.view', 'bookings.view-own'],
        ],
        [
            'key' => 'collections',
            'label' => 'nav.collections',
            'icon' => 'cash-register',
            'route' => 'admin.dashboard',
            'permissions' => ['collections.view'],
        ],
        [
            'key' => 'website',
            'label' => 'nav.website',
            'icon' => 'building',
            'route' => 'admin.dashboard',
            'permissions' => ['website.edit'],
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
