<?php

require __DIR__.'/../vendor/autoload.php';


class MyEcho extends Allmy\Protocol\BaseProtocol {
    public function dataReceived($data) {
        $this->transport->write($data);
    }
}

class EchoFactory extends Allmy\Protocol\ProtocolFactory {
    public function buildProtocol($addr)
    {
        return new MyEcho();
    }
}

$reactor = Allmy\Reactor\Factory::create();
$reactor->listenTcp('12345', new EchoFactory());
$reactor->run();

