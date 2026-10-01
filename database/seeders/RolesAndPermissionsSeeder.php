<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // تصفير كاش الصلاحيات قبل ما نبدأ
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // -------------------------------------------------------------
        // الصلاحيات (Permissions) - موديول متابعة التكليفات
        // -------------------------------------------------------------
        $permissions = [
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // -------------------------------------------------------------
        // الأدوار (Roles) وربطها بالصلاحيات المناسبة
        // -------------------------------------------------------------

        // 1) مشرف عام - كل الصلاحيات
        $admin = Role::firstOrCreate(['name' => 'مشرف عام']);
        $admin->syncPermissions(Permission::all());

        // 2) مسؤول التكليفات - كل صلاحيات التكليفات
        $assignmentManager = Role::firstOrCreate(['name' => 'مسؤول تكليفات']);
        $assignmentManager->syncPermissions($permissions);

        // 3) مدير - عرض فقط
        $manager = Role::firstOrCreate(['name' => 'مدير']);
        $manager->syncPermissions(['tasks.view']);

        // 4) موظف - عرض فقط
        $staff = Role::firstOrCreate(['name' => 'موظف']);
        $staff->syncPermissions(['tasks.view']);
    }
}
