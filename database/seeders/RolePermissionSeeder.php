<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission catalogue grouped by module.
     *
     * @var array<string, list<string>>
     */
    public const PERMISSIONS = [
        'dashboard' => ['view'],
        'property' => ['view', 'create', 'update'],
        'building' => ['view', 'create', 'update', 'delete'],
        'floor' => ['view', 'create', 'update', 'delete'],
        'room_type' => ['view', 'create', 'update', 'delete'],
        'room' => ['view', 'create', 'update', 'delete'],
        'amenity' => ['view', 'create', 'update', 'delete'],
        'tenant' => ['view', 'create', 'update'],
        'tenant_document' => ['view', 'create', 'delete'],
        'lease' => ['view', 'create', 'update', 'terminate'],
        'invoice' => ['view', 'create', 'update', 'void'],
        'payment' => ['view', 'create', 'verify', 'reject'],
        'expense' => ['view', 'create', 'update', 'delete'],
        'maintenance' => ['view', 'create', 'update', 'assign', 'close'],
        'announcement' => ['view', 'create', 'update', 'delete'],
        'report' => ['view', 'export'],
        'user' => ['view', 'create', 'update', 'delete'],
        'role' => ['view', 'update'],
        'settings' => ['view', 'update'],
        'audit' => ['view'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = [];

        foreach (self::PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                Permission::findOrCreate($name, 'web');
                $all[] = $name;
            }
        }

        $roles = [
            'owner' => $all,
            'admin' => [
                'dashboard.view',
                'property.view', 'property.create', 'property.update',
                'building.view', 'building.create', 'building.update', 'building.delete',
                'floor.view', 'floor.create', 'floor.update', 'floor.delete',
                'room_type.view', 'room_type.create', 'room_type.update', 'room_type.delete',
                'room.view', 'room.create', 'room.update', 'room.delete',
                'amenity.view', 'amenity.create', 'amenity.update', 'amenity.delete',
                'tenant.view', 'tenant.create', 'tenant.update',
                'tenant_document.view', 'tenant_document.create', 'tenant_document.delete',
                'lease.view', 'lease.create', 'lease.update', 'lease.terminate',
                'invoice.view', 'invoice.create', 'invoice.update',
                'payment.view', 'payment.create', 'payment.verify', 'payment.reject',
                'expense.view', 'expense.create', 'expense.update',
                'maintenance.view', 'maintenance.create', 'maintenance.update', 'maintenance.assign', 'maintenance.close',
                'announcement.view', 'announcement.create', 'announcement.update', 'announcement.delete',
                'report.view', 'report.export',
                'user.view', 'user.create', 'user.update',
                'settings.view',
                'audit.view',
            ],
            'finance' => [
                'dashboard.view',
                'tenant.view',
                'tenant_document.view',
                'lease.view',
                'invoice.view', 'invoice.create', 'invoice.update', 'invoice.void',
                'payment.view', 'payment.create', 'payment.verify', 'payment.reject',
                'expense.view', 'expense.create', 'expense.update', 'expense.delete',
                'report.view', 'report.export',
            ],
            'technician' => [
                'dashboard.view',
                'room.view',
                'tenant.view',
                'maintenance.view', 'maintenance.create', 'maintenance.update', 'maintenance.assign', 'maintenance.close',
            ],
            'tenant' => [
                'dashboard.view',
                'lease.view',
                'invoice.view',
                'payment.view', 'payment.create',
                'maintenance.view', 'maintenance.create',
                'announcement.view',
            ],
        ];

        foreach ($roles as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
