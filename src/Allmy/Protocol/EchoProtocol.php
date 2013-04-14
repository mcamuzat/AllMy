<?php

namespace Allmy\Protocol;

class EchoProtocol  extends BaseProtocol{
    function dataReceived($data) {
        echo 'hahah';
        $this->transport->write($data);
    }
}

