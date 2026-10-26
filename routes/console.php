<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('propix:release-expired-holds')->everyFifteenMinutes();
Schedule::command('backup:run --only-db')->daily()->at('01:00');
Schedule::command('backup:clean')->weekly();
