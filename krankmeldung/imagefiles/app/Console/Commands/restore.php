<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

function getBackups()
{
    $dir = storage_path('backups');
    return $files = collect(\File::allFiles($dir))
        ->filter(function ($file) {
            return in_array($file->getExtension(), ['sql']);
        })
        ->sortByDesc(function ($file) {
            return $file->getCTime();
        })->values()
        ->map(function ($file) {
            return [$file->getBaseName(), $file->getCTime()];
        });
}

class restore extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "app:restore {file=default}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore data to Database use "app:restore file=xxx.sql"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->argument('file')=="default") {
            $files=getBackups();
            $file = storage_path('backups/'.$files[0][0]);
        }
        else {
            $file = $this->argument('file');
        }
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $file
        );
        system($command);
    }
}
