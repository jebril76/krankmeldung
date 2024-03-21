<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Mail\BackupMailer;

class SendEMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sendemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup reports to Email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('db:backup');
        $mailData = [
            'subject' => 'Krankmeldungen bis zum '.date('d.m.Y H:i:s'),
            'date' => date('d.m.Y'),
            'sql' => storage_path('backups/backup-'.date('Y-m-d-H-i-s').'.sql'),
            'name' => "backup-".date('Y-m-d-H-i-s').".sql"
        ];
        Mail::to(config('custom.backupmail'))
            ->send(new BackupMailer($mailData));
    }
}
