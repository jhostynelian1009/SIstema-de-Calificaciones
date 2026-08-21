<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin {--name=} {--email=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un usuario administrador inicial de forma segura para producción';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        if ($name === null && $this->input->isInteractive()) {
            $name = $this->ask('Ingrese el nombre completo del administrador');
        }

        if (! $name) {
            $this->error('El nombre del administrador es obligatorio.');

            return self::FAILURE;
        }

        if ($email === null && $this->input->isInteractive()) {
            $email = $this->ask('Ingrese el correo electrónico del administrador');
        }

        $emailValidator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email|unique:users,email']
        );

        if ($emailValidator->fails()) {
            foreach ($emailValidator->errors()->all() as $err) {
                $this->error($err);
            }

            return self::FAILURE;
        }

        if (! $password && $this->input->isInteractive()) {
            $password = $this->secret('Ingrese la contraseña (mínimo 8 caracteres)');
            $passwordConfirm = $this->secret('Confirme la contraseña');

            while (strlen((string) $password) < 8 || $password !== $passwordConfirm) {
                if (strlen((string) $password) < 8) {
                    $this->error('La contraseña debe tener al menos 8 caracteres.');
                } elseif ($password !== $passwordConfirm) {
                    $this->error('Las contraseñas no coinciden.');
                }
                $password = $this->secret('Ingrese la contraseña (mínimo 8 caracteres)');
                $passwordConfirm = $this->secret('Confirme la contraseña');
            }
        }

        $passValidator = Validator::make(
            ['password' => $password],
            ['password' => 'required|min:8']
        );

        if ($passValidator->fails()) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');

            return self::FAILURE;
        }

        $admin = User::create([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $this->info("Usuario administrador creado exitosamente: {$admin->email}");

        return self::SUCCESS;
    }
}
