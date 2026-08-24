<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PrepareRealDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Note: Intentionally no --force option to prevent accidental or automated bypass.
     *
     * @var string
     */
    protected $signature = 'app:prepare-real-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepara la base de datos de desarrollo para datos reales eliminando la información demostrativa tras verificar respaldo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('ERROR CRÍTICO: Este comando está estrictamente prohibido en entornos de producción.');

            return self::FAILURE;
        }

        if (! app()->environment('local')) {
            $this->error('ERROR: Este comando solo puede ejecutarse en el entorno "local".');

            return self::FAILURE;
        }

        $connection = config('database.default');
        $dbName = config("database.connections.{$connection}.database");

        if (str_ends_with((string) $dbName, '_test')) {
            $this->error("ERROR: No se permite ejecutar este comando sobre una base de datos de pruebas ('{$dbName}').");

            return self::FAILURE;
        }

        if ($dbName !== 'sistema_calificaciones') {
            $this->error("ERROR: Base de datos no autorizada ('{$dbName}'). Solo se permite 'sistema_calificaciones'.");

            return self::FAILURE;
        }

        $this->warn("Base de datos seleccionada: {$dbName}");

        $backupDir = storage_path('app/private/backups');
        $backupFiles = File::exists($backupDir) ? File::glob("{$backupDir}/pre_real_data_*.sql") : [];
        $validBackup = false;

        foreach ($backupFiles as $file) {
            if (File::size($file) > 0) {
                $validBackup = true;
                $this->info('Respaldo SQL previo verificado correctamente: '.basename($file));
                break;
            }
        }

        if (! $validBackup) {
            $this->error('ERROR: No se encontró ningún respaldo SQL previo válido en storage/app/private/backups/. Operación cancelada por seguridad.');

            return self::FAILURE;
        }

        $expectedPhrase = "BORRAR DEMO {$dbName}";

        $input = $this->ask("ADVERTENCIA DE SEGURIDAD:\nSe eliminarán TODOS los datos demostrativos aplicando un esquema limpio.\nEscriba exactamente la siguiente frase para confirmar:\n{$expectedPhrase}");

        if ($input !== $expectedPhrase) {
            $this->error('Frase de confirmación incorrecta. Operación cancelada sin modificar datos.');

            return self::FAILURE;
        }

        $this->info('Confirmación válida. Ejecutando migración limpia (sin seeders demo)...');

        $this->call('migrate:fresh');

        $this->info('Base de datos preparada exitosamente con estructura limpia.');
        $this->warn('PASO SIGUIENTE OBLIGATORIO: Ejecute inmediatamente "php artisan app:create-admin" para crear el primer usuario administrador real.');

        return self::SUCCESS;
    }
}
