<?php
/**
 * TCP client endpoint with an IPv4 configuration.
 */
class TCP4ClientEndpoint implements IStreamClientEndpoint {
        public $reactor;
        public $host;
        public $port;
        public $timeout;
        public $bindAddress;

    public function __construct($reactor, $host, $port, $timeout=30, $bindAddress=null) 
    {
        /**
        @param reactor: An L{IReactorTCP} provider

        @param host: A hostname, used when connecting
        @type host: str

        @param port: The port number, used when connecting
        @type port: int

        @param timeout: The number of seconds to wait before assuming the
            connection has failed.
        @type timeout: int

        @param bindAddress: A (host, port) tuple of local address to bind to,
            or None.
        @type bindAddress: tuple
         */
        $this->reactor = $reactor;
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->bindAddress = $bindAddress;
    }

    public function connect($protocolFactory) {
        /**
        Implement L{IStreamClientEndpoint.connect} to connect via TCP.
         */
        try(
            $wf = new WrappingFactory($protocolFactory);
        $this->reactor->connectTCP(
            $this->host, $this->port, $wf,
            $this->timeout, bindAddress=$this->bindAddress)
            return $wf->onConnection;
        ) catch (Exception $e) {
            //return Dedefer->fail();

        }
    }
}
