<?php

require __DIR__.'/../vendor/autoload.php';

class QOTDFactory extends Allmy\Protocol\ProtocolFactory {
    public function buildProtocol($addr)
    {
        return new Allmy\Protocol\QOTD();
    }
}

$reactor = Allmy\Reactor\Factory::create();
$reactor->listenTcp('12345', new QOTDFactory());
$reactor->run();

