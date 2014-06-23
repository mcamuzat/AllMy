<?php

require __DIR__.'/../vendor/autoload.php';


class Tiny extends Allmy\Protocol\LineReceiver {
    public $answer = array("name"=> "why?", "default" => "I don't know what you mean");
    public function lineReceived($line) {
        if (isset ($this->answer[$line])) {
            $this->sendLine($this->answer[$line]);
        } else {
            $this->sendLine($this->answer['default']);
        }
    }
}

class TinyFactory extends Allmy\Protocol\ProtocolFactory {
    public function buildProtocol($addr)
    {
        return new Tiny();
    }
}

$reactor = Allmy\Reactor\Factory::create();
$reactor->listenTcp('12345', new TinyFactory());
$reactor->run();

