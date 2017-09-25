<?php

use Crunz\Schedule;

$schedule = new Schedule();
$schedule->run('php bin/mirror create')
         ->name('create/update mirror')
         ->everyMinute()
         ->preventOverlapping();

return $schedule;
