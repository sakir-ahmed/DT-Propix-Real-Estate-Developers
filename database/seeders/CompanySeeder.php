<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Setup\SeedCompanyRolesAction;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * One installation is one company. Seeds the roles, the head office branch,
 * the first owner and the hidden Datatech super-admin with fixed starter
 * credentials. Nothing that already exists is overwritten, so re-seeding keeps
 * the passwords and details the company changed after the first sign-in.
 */
class CompanySeeder extends Seeder
{
    public const string DEFAULT_PASSWORD = 'password';

    /** @var array<string, string> */
    public const array SUPER_ADMIN = [
        'name' => 'Datatech Super Admin',
        'username' => 'superadmin',
        'email' => 'superadmin@datatech.test',
        'mobile' => '8801700000000',
    ];

    /** @var array<string, string> */
    public const array OWNER = [
        'name' => 'Company Owner',
        'username' => 'owner',
        'email' => 'owner@company.test',
        'mobile' => '8801710000001',
    ];

    public function run(): void
    {
        app(SeedCompanyRolesAction::class)->handle();

        $headOffice = Branch::query()->firstOrCreate(
            ['code' => 'HO'],
            [
                'name' => 'Head office',
                'address' => null,
                'is_head_office' => true,
            ],
        );

        Branch::query()->firstOrCreate(
            ['code' => 'CTG'],
            [
                'name' => 'Chattogram branch',
                'address' => null,
                'is_head_office' => false,
            ],
        );

        $this->seedSuperAdmin();
        $this->seedOwner($headOffice);
    }

    private function seedSuperAdmin(): void
    {
        User::query()->firstOrCreate(
            ['is_super_admin' => true],
            [
                'branch_id' => null,
                'name' => self::SUPER_ADMIN['name'],
                'username' => self::SUPER_ADMIN['username'],
                'email' => self::SUPER_ADMIN['email'],
                'mobile' => normalize_bd_mobile(self::SUPER_ADMIN['mobile']),
                'password' => self::DEFAULT_PASSWORD,
                'locale' => 'en',
            ],
        );
    }

    private function seedOwner(Branch $headOffice): void
    {
        $owner = User::query()->firstOrCreate(
            ['username' => self::OWNER['username']],
            [
                'branch_id' => $headOffice->id,
                'name' => self::OWNER['name'],
                'email' => self::OWNER['email'],
                'mobile' => normalize_bd_mobile(self::OWNER['mobile']),
                'password' => self::DEFAULT_PASSWORD,
                'locale' => 'en',
                'is_super_admin' => false,
            ],
        );

        if (! $owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }
    }
}
