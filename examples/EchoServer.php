<?php

require __DIR__.'/../vendor/autoload.php';


class EchoFactory extends Allmy\Protocol\ProtocolFactory {
    public function buildProtocol($addr)
    {
        return new Allmy\Protocol\EchoProtocol();
    }
}

$reactor = Allmy\Reactor\Factory::create();
$reactor->listenTcp('12345', new EchoFactory());
$reactor->run();

