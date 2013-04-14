<?php

namespace Allmy\Protocol;

class EchoProtocol  extends BaseProtocol{
    function dataReceived($data) {
        $this->transport->write($data);
    }
}

