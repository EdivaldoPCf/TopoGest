<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Os comandos de importação e limpeza foram movidos para classes dedicadas em
| app/Console/Commands (auto-descobertas pelo Laravel):
|   - bases:import         -> ImportarBasesCommand
|   - bases:clean          -> LimparBasesCommand
|   - pastas:import        -> ImportarFluxoPastasCommand
|   - pastas:sync-pendentes -> SyncPendentesCommand
*/

Schedule::command('pastas:sync-pendentes')
    ->hourly()
    ->between('00:00', '06:00')
    ->appendOutputTo(storage_path('logs/sync_pendentes.log'));
