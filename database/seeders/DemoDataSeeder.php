<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Collections\RecordCollectionAction;
use App\Actions\Crm\CaptureLeadAction;
use App\Actions\Documents\GenerateDocumentNumberAction;
use App\Actions\Inventory\ChangeUnitStatusAction;
use App\Actions\Sales\CreateBookingAction;
use App\Enums\UnitStatus;
use App\Models\Customer;
use App\Models\PaymentPlanTemplate;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo content for local development and the demo login: one project with
 * units, leads with activities and visits, customers, bookings and receipts
 * so the funnel dashboard shows real numbers.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('username', 'owner')->firstOrFail();

        $template = PaymentPlanTemplate::query()->updateOrCreate(
            ['name' => 'Booking 10, DP 15, 60 installments, handover 15'],
            [
                'lines' => [
                    ['label' => 'Booking money', 'kind' => 'booking_money', 'percent' => 10, 'due_days' => 0],
                    ['label' => 'Down payment', 'kind' => 'down_payment', 'percent' => 15, 'due_days' => 30],
                    ['label' => 'Installments (60 months)', 'kind' => 'installment', 'percent' => 60, 'due_days' => 90],
                    ['label' => 'Handover payment', 'kind' => 'handover', 'percent' => 15, 'due_days' => 1095],
                ],
            ],
        );

        $project = Project::query()->updateOrCreate(
            ['slug' => 'sky-garden-chattogram'],
            [
                'name' => 'Sky Garden Chattogram',
                'type' => 'apartment',
                'status' => 'ongoing',
                'location' => 'Khulshi, Chattogram',
                'launch_date' => now()->subYear()->toDateString(),
                'handover_date' => now()->addYears(2)->toDateString(),
                'description_en' => 'A 12-storey residential project with 24 apartments.',
                'description_bn' => '২৪টি অ্যাপার্টমেন্টের ১২ তলা আবাসিক প্রজেক্ট।',
            ],
        );

        if (Unit::query()->where('project_id', $project->id)->doesntExist()) {
            foreach (range(3, 6) as $floor) {
                foreach (range(1, 4) as $column) {
                    Unit::query()->create([
                        'project_id' => $project->id,
                        'number' => 'SG-'.$floor.str_pad((string) $column, 2, '0', STR_PAD_LEFT),
                        'type' => 'apartment',
                        'size_sqft' => '1450.00',
                        'facing' => $column % 2 === 0 ? 'south' : 'north',
                        'bedrooms' => 3,
                        'bathrooms' => 2,
                        'parking_count' => 1,
                        'base_rate' => '7500.00',
                        'floor_premium' => '60000.00',
                        'parking_price' => '350000.00',
                        'utility_charges' => '120000.00',
                        'other_charges' => '0.00',
                        'status' => 'available',
                    ]);
                }
            }
        }

        $capture = app(CaptureLeadAction::class);

        $capture->handle(['name' => 'Rafiq Uddin', 'mobile' => '01711000001', 'source' => 'website', 'project_id' => $project->id], $owner);
        $capture->handle(['name' => 'Nusrat Jahan', 'mobile' => '01811000002', 'source' => 'walk-in', 'project_id' => $project->id], $owner);
        $capture->handle(['name' => 'Mahin Hasan', 'mobile' => '01911000003', 'source' => 'broker'], $owner);

        $customerIds = [];
        $nextCode = app(GenerateDocumentNumberAction::class);

        foreach ([['Rafiq Uddin', '01711000001'], ['Nusrat Jahan', '01811000002']] as [$name, $mobile]) {
            $customer = Customer::query()->firstOrCreate(
                ['mobile' => normalize_bd_mobile($mobile)],
                [
                    'branch_id' => $owner->branch_id,
                    'customer_code' => $nextCode->handle('C'),
                    'name' => $name,
                    'email' => null,
                    'address' => 'Chattogram',
                ],
            );
            $customerIds[] = $customer->id;
        }

        $bookingAction = app(CreateBookingAction::class);
        $collectionAction = app(RecordCollectionAction::class);

        $units = Unit::query()->where('project_id', $project->id)->available()->orderBy('number')->get();

        if ($units->count() >= 2 && Customer::query()->count() >= 2) {
            $customers = Customer::query()->orderBy('id')->get();

            $first = $bookingAction->handle($units->get(0), $customers[0], $owner, ['template_id' => $template->id]);
            $collectionAction->handle($first, $owner, ['method' => 'cash', 'amount' => '130000.00']);

            $second = $bookingAction->handle($units->get(1), $customers[1], $owner, ['template_id' => $template->id]);
            $second->update(['agreement_signed_at' => now()]);
            app(ChangeUnitStatusAction::class)->handle(
                $second->unit,
                UnitStatus::Sold,
                $owner,
                'agreement '.$second->booking_no,
            );
            $collectionAction->handle($second, $owner, ['method' => 'bank', 'amount' => '950000.00', 'reference' => 'TRX-8891']);
        }

        // A repeat enquiry from an existing mobile adds an activity, not a duplicate.
        $capture->handle(['name' => 'Rafiq Uddin', 'mobile' => '01711000001', 'source' => 'website', 'note' => 'Asked about parking'], $owner);
    }
}
