<?php
require __DIR__.'/../vendor/autoload.php';

$loop = Allmy\Reactor\Factory::create();
$loop->addPeriodicTimer(1, function () {echo 'je suis connecté';}, true);
$loop->addReadStream(file_get_contents('php://stdin'));
$loop->run();
