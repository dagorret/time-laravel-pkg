<?php

namespace Time\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class GenerateTableConfig extends Command
{
    /**
     * La firma del comando. 
     * --seed=1000 es el valor por defecto si solo pones --seed
     */
    protected $signature = 'time:table {--seed=1000 : ¿Cuántos registros de prueba generar?}';

    /**
     * La descripción del comando en la lista de artisan.
     */
    protected $description = 'Prepara el laboratorio para pruebas de tablas lazy con fechas';

    /**
     * Ejecutar el comando.
     */
    public function handle()
    {
        $this->info('🛠️  Preparando tabla de pruebas...');

        // 1. Ejecutar las migraciones que tenga el paquete
        // Esto buscará en la carpeta que definimos en el ServiceProvider
        $this->call('migrate');

        // 2. Obtener el valor de la opción seed
        // Si el usuario no pone el flag, es null. Si lo pone sin valor, es 1000.
        $count = $this->option('seed');

        if ($count !== null) {
            $count = (int) $count;
            if ($count > 0) {
                $this->generateData($count);
            }
        }

        $this->info('✅ Entorno listo. La tabla "time_test_logs" está operativa.');
        
        return self::SUCCESS;
    }

    /**
     * Lógica para generar datos aleatorios de tiempo.
     */
    protected function generateData($count)
    {
        $this->info("Generando {$count} registros de prueba...");
        
        $this->output->progressStart($count);
        
        for ($i = 0; $i < $count; $i++) {
            // Generamos una fecha aleatoria dentro del último año
            $date = Carbon::now()->subDays(rand(0, 365))->subHours(rand(0, 23));
            
            DB::table('time_test_logs')->insert([
                'event_name'  => 'Evento de prueba #' . ($i + 1),
                'status'      => ['success', 'warning', 'error'][rand(0, 2)],
                'started_at'  => $date,
                'finished_at' => $date->copy()->addMinutes(rand(5, 120)),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("🚀 {$count} registros insertados correctamente.");
    }
}
