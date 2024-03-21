<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Backup the database';

    protected $process;

    public function handle()
    {
        $command = sprintf(
                'mysqldump --no-create-info --replace --host=%s --user=%s --password=%s %s %s > %s',
                config('database.connections.mysql.host'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                'reports',
                storage_path('backups/backup-'.date("Y-m-d-H-i-s").'.sql')
            );
        system($command);
    }
}