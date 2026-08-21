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
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Los seeders de datos demostrativos se encuentran deshabilitados en producción.');

            return;
        }

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

        // Teacher 1
        User::updateOrCreate(
            ['email' => 'docente@calificaciones.local'],
            [
                'name' => 'Docente Demo 1',
                'password' => $passwordHash,
                'role' => UserRole::Teacher,
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Teacher 2
        User::updateOrCreate(
            ['email' => 'docente2@calificaciones.local'],
            [
                'name' => 'Docente Demo 2',
                'password' => $passwordHash,
                'role' => UserRole::Teacher,
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Active Students (1 to 6)
        $studentNames = [
            1 => 'Carlos Andrade',
            2 => 'María Beltrán',
            3 => 'David Cárdenas',
            4 => 'Elena Delgado',
            5 => 'Fernando Espinoza',
            6 => 'Gabriela Flores',
        ];

        foreach ($studentNames as $i => $name) {
            User::updateOrCreate(
                ['email' => "estudiante{$i}@calificaciones.local"],
                [
                    'name' => $name,
                    'password' => $passwordHash,
                    'role' => UserRole::Student,
                    'active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Backward compatibility alias for 'estudiante@calificaciones.local' used in K-002 tests
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
            EnrollmentSeeder::class,
            TeachingAssignmentSeeder::class,
            PartialPublicationSeeder::class,
            ActivitySeeder::class,
            GradeSeeder::class,
            PublicationSeeder::class,
        ]);
    }
}
