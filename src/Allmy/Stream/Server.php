<?php


namespace Allmy\Stream;

/**
 * Serverside socket-stream connection class.
 * 
 * This is a serverside network connection transport; a socket which came from
 * an accept() on a server.
 * 
 * @ivar _base: L{Connection}, which is the base class of this class which has
 * all of the useful file descriptor methods.  This is used by
 * L{_TLSServerMixin} to call the right methods to directly manipulate the
 * transport, as is necessary for writing TLS-encrypted bytes (whereas
 * those methods on L{Server} will go through another layer of TLS if it
 * has been enabled).
 */

class Server extends Connection {
    public $_base = 'Connection';

    //_addressType = address.IPv4Address
    /**
     * Server(sock, protocol, client, server, sessionno)

     * Initialize it with a socket, a protocol, a descriptor for my peer (a
     * tuple of host, port describing the other end of the connection), an
     * instance of Port, and a session number.
     */
    public function __construct($sock, $protocol, $client, $server, $sessionno, $reactor) {
        parent::__construct($sock, $protocol, $reactor);
        /*if len(client) != 2:
            $this->_addressType = address.IPv6Address*/
        $this->server = $server;
        $this->client = $client;
        $this->sessionno = $sessionno;
        $this->hostname = $client[0];

        //$logPrefix = $this->_getLogPrefix($this->protocol);


       // $this->logstr = printf("%s,%s,%s", $logPrefix,$sessionno,$this->hostname);
      /*  if $this->server is not None:
       /*     $this->repstr = "<%s #%s on %s>" % ($this->protocol.__class__.__name__,
                                              $this->sessionno,
                                              $this->server._realPortNumber)*/
        $this->startReading();
        $this->connected = 1;
    }
    public function __toString() {
        /**
        A string representation of this connection.
        */
        return $this->logstr;
    }

/**
    @classmethod
    def _fromConnectedSocket(cls, fileDescriptor, addressFamily, factory,
                             reactor):
        /**
        Create a new L{Server} based on an existing connected I{SOCK_STREAM}
        socket.

        Arguments are the same as to L{Server.__init__}, except where noted.

        @param fileDescriptor: An integer file descriptor associated with a
            connected socket.  The socket must be in non-blocking mode.  Any
            additional attributes desired, such as I{FD_CLOEXEC}, must also be
            set already.

        @param addressFamily: The address family (sometimes called I{domain})
            of the existing socket.  For example, L{socket.AF_INET}.

        @return: A new instance of C{cls} wrapping the socket given by
            C{fileDescriptor}.
        addressType = address.IPv4Address
        if addressFamily == socket.AF_INET6:
            addressType = address.IPv6Address
        skt = socket.fromfd(fileDescriptor, addressFamily, socket.SOCK_STREAM)
        addr = skt.getpeername()
        protocolAddr = addressType('TCP', addr[0], addr[1])
        localPort = skt.getsockname()[1]

        protocol = factory.buildProtocol(protocolAddr)
        if protocol is None:
            skt.close()
            return

        self = cls(skt, protocol, addr, None, addr[1], reactor)
        $this->repstr = "<%s #%s on %s>" % (
            $this->protocol.__class__.__name__, $this->sessionno, localPort)
        protocol.makeConnection(self)
        return self
        /**
        Returns an L{IPv4Address} or L{IPv6Address}.

        This indicates the server's address.
        */

    public function getHost() {
    //    host, port = $this->socket.getsockname()[:2]
    //    return $this->_addressType('TCP', host, port)
    }
        /**
        Returns an L{IPv4Address} or L{IPv6Address}.

        This indicates the client's address.
        */
    public function getPeer() {
//    return $this->_addressType('TCP', *self.client[:2]);
    }
}
