<?php

require __DIR__.'/../vendor/autoload.php';

class ChargenFactory extends Allmy\Protocol\ProtocolFactory {
    public function buildProtocol($addr)
    {
        return new Allmy\Protocol\Chargen();
    }
}

$reactor = Allmy\Reactor\Factory::create();
$reactor->listenTcp('12345', new ChargenFactory());
$reactor->run();

