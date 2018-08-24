<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LegacyInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install i-Educar legacy code';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = env('LEGACY_PATH');

        passthru('chmod +x vendor/portabilis/jasperphp/src/JasperStarter/bin/jasperstarter');
        passthru('chmod -R 777 bootstrap/cache');
        passthru('chmod -R 777 storage');
        passthru(
            sprintf('chmod -R 777 %s/modules/Reports/ReportSources/Portabilis', $path)
        );

        $this->call('key:generate');
        $this->call('legacy:link');

        passthru('vendor/bin/phinx seed:run -s StartingSeed -s StartingForeignKeysSeed');
        passthru('vendor/bin/phinx migrate');
    }
}
