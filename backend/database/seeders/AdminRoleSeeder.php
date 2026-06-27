<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin (already exists, just ensure role is set)
        $superAdmin = AdminUser::where('email', 'admin@kin.com')->first();
        if ($superAdmin) {
            $superAdmin->role = 'super_admin';
            $superAdmin->save();
            $this->command->info('✅ Super Admin role updated');
        }

        // Support Admin
        $supportAdmin = AdminUser::where('email', 'support@kin.com')->first();
        if (!$supportAdmin) {
            $supportAdmin = new AdminUser();
            $supportAdmin->name = 'Support Admin';
            $supportAdmin->email = 'support@kin.com';
            $supportAdmin->password = Hash::make('password');
            $supportAdmin->role = 'support_admin';
            $supportAdmin->is_active = true;
            $supportAdmin->save();
            $this->command->info('✅ Support Admin created');
        } else {
            $supportAdmin->role = 'support_admin';
            $supportAdmin->save();
            $this->command->info('✅ Support Admin role updated');
        }

        // Viewer Admin
        $viewerAdmin = AdminUser::where('email', 'viewer@kin.com')->first();
        if (!$viewerAdmin) {
            $viewerAdmin = new AdminUser();
            $viewerAdmin->name = 'Viewer Admin';
            $viewerAdmin->email = 'viewer@kin.com';
            $viewerAdmin->password = Hash::make('password');
            $viewerAdmin->role = 'viewer_admin';
            $viewerAdmin->is_active = true;
            $viewerAdmin->save();
            $this->command->info('✅ Viewer Admin created');
        } else {
            $viewerAdmin->role = 'viewer_admin';
            $viewerAdmin->save();
            $this->command->info('✅ Viewer Admin role updated');
        }

        $this->command->info('');
        $this->command->info('📋 Admin Credentials:');
        $this->command->info('Super Admin: admin@kin.com / password');
        $this->command->info('Support Admin: support@kin.com / password');
        $this->command->info('Viewer Admin: viewer@kin.com / password');
    }
}
