Allmy
=====

A mix between Reactphp and Twisted

the hello world in Twisted is

'''py
from twisted.internet import protocol, reactor

class Echo(protocol.Protocol):
    def dataReceived(self, data):
        self.transport.write(data)

class EchoFactory(protocol.Factory):
    def buildProtocol(self, addr):
        return Echo()

reactor.listenTCP(1234, EchoFactory())
reactor.run()
'''

in Allmy
'''php
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
'''

huge amount of copy/paste from ReactPhp/Twisted.

# TODO
for now it's just work with the stream_select()
