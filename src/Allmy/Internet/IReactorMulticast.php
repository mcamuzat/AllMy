<?php 
    /**
    UDP socket methods that support multicast.

    IMPORTANT: This is an experimental new interface. It may change
    without backwards compatability. Suggestions are welcome.
    */


interface IReactorMulticast{
    public function listenMulticast(port, protocol, interface='', maxPacketSize=8192,
                        listenMultiple=False);
        /**
        Connects a given
        L{DatagramProtocol<twisted.internet.protocol.DatagramProtocol>} to the
        given numeric UDP port.

        @param listenMultiple: If set to True, allows multiple sockets to
            bind to the same address and port number at the same time.
        @type listenMultiple: C{bool}

        @returns: An object which provides L{IListeningPort}.

        @see: L{twisted.internet.interfaces.IMulticastTransport}
        @see: U{http://twistedmatrix.com/documents/current/core/howto/udp.html}
        */
}
