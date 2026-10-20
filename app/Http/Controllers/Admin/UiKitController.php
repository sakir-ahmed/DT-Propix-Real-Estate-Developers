<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class UiKitController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/UiKit', [
            'components' => [
                'AppIcon',
                'CustomerBanner',
                'UnitGrid',
                'UnitStatusBadge',
                'PipelineBoard',
                'ScheduleTable',
                'PriceBreakdown',
                'ActivityTimeline',
                'ApprovalTimeline',
                'DocumentList',
                'ProjectPicker',
                'PageHeader',
                'ConfirmDialog',
                'ReasonDialog',
                'ApprovalDialog',
                'TypedConfirmDialog',
                'SlideOver',
                'FormField',
                'MoneyInput',
                'MobileInput',
                'QuantityStepper',
                'Combobox',
                'MultiSelect',
                'DateRangePicker',
                'FileUpload',
                'DataTable',
                'FilterBar',
                'StatusBadge',
                'KpiCard',
                'ChartCard',
                'EmptyState',
                'ErrorState',
                'SkeletonRows',
                'MoneyText',
                'DateText',
                'NotificationBell',
                'CommandPalette',
                'ThemeToggle',
                'LanguageSwitch',
                'ErrorPage',
            ],
        ]);
    }
}
