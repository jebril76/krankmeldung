<?php
return [
    'backupmail' => env('MAIL_BACKUP_TO_ADDRESS', env('MAIL_FROM_ADDRESS','')),
    'backuptime' => env('MAIL_BACKUP_TIME', '* * * * *'),
    'emailhead' => env('EMAILHEAD', 'Backup der Krankmeldungen bis zum'),
    'emailbody' => env('EMAILBODY', 'Backup der Reports als SQL-File.'),
    'infoscreenspeed' => env('INFOSCREENSPEED', '1000'),
];
