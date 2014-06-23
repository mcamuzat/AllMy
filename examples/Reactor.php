<?php
require __DIR__.'/../vendor/autoload.php';

$loop = Allmy\Reactor\Factory::create();
$loop->callWhenRunning(function () {echo 'je suis connecté';});
$loop->run();
