<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions list
        $permissions = [
            'manage users',
            'manage roles',
            'manage settings',
            'manage theme',
            'manage seo',
            'manage ads',
            'manage affiliates',
            'manage pages',
            'manage articles',
            'publish articles',
            'manage categories',
            'manage tags',
            'manage comments',
            'manage media',
            'view analytics',
            'view logs',
            'manage security',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 1. Super Admin (Full Unrestricted Access)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Also ensure alias 'super-admin' gets all permissions for compatibility
        $superAdminSlug = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminSlug->syncPermissions(Permission::all());

        // 2. Admin (Content, Monetization, SEO, Growth, Analytics)
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'manage articles',
            'publish articles',
            'manage categories',
            'manage tags',
            'manage media',
            'manage comments',
            'manage pages',
            'manage ads',
            'manage affiliates',
            'manage seo',
            'view analytics',
        ]);

        // 3. Editor (Content Management, Pages, Categories, Moderation)
        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'manage articles',
            'publish articles',
            'manage categories',
            'manage tags',
            'manage media',
            'manage comments',
            'manage pages',
        ]);

        // 4. Author (Create and edit own content & media)
        $author = Role::firstOrCreate(['name' => 'Author', 'guard_name' => 'web']);
        $author->syncPermissions([
            'manage articles',
            'manage tags',
            'manage media',
        ]);
    }
}
