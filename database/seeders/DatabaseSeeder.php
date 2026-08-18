<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database idempotently.
     */
    public function run(): void
    {
        $passwordHash = Hash::make('Password123!');

        // Administrator
        User::updateOrCreate(
            ['email' => 'admin@calificaciones.local'],
            [
                'name' => 'Administrador Demo',
                'password' => $passwordHash,
                'role' => UserRole::Admin,
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Teacher
        User::updateOrCreate(
            ['email' => 'docente@calificaciones.local'],
            [
                'name' => 'Docente Demo',
                'password' => $passwordHash,
                'role' => UserRole::Teacher,
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Student
        User::updateOrCreate(
            ['email' => 'estudiante@calificaciones.local'],
            [
                'name' => 'Estudiante Demo',
                'password' => $passwordHash,
                'role' => UserRole::Student,
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Inactive User
        User::updateOrCreate(
            ['email' => 'inactivo@calificaciones.local'],
            [
                'name' => 'Usuario Inactivo Demo',
                'password' => $passwordHash,
                'role' => UserRole::Student,
                'active' => false,
                'email_verified_at' => now(),
            ]
        );

        // Call Academic Seeders
        $this->call([
            CourseSeeder::class,
            SubjectSeeder::class,
            AcademicPeriodSeeder::class,
        ]);
    }
}
