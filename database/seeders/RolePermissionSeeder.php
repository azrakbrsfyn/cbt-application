<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat permission
        $permissions = [
            'view courses',
            'edit courses',
            'delete courses',
            'create courses',
        ];

        // Menyimpan permission ke database table Permission
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission
            ]);
        }

        // Membuat role teacher
        $teacherRole = Role::create([
            'name' => 'teacher'
        ]);

        // Memasukkan apa-apa saja yang boleh dilakukan oleh role teacher
        $teacherRole->givePermissionTo([
            'view courses',
            'edit courses',
            'delete courses',
            'create courses',
        ]);

        // Membuat role student
        $studentRole = Role::create([
            'name' => 'student'
        ]);

        $studentRole->givePermissionTo([
            'view courses'
        ]);

        // Membuat akun super admin secara default
        $user = User::create([
            'name' => 'Azriel',
            'email' => 'azrielakbarsofyan@gmail.com',
            'password' => bcrypt('allahtuhanku1')
        ]);

        $user->assignRole($teacherRole);
    }
}
