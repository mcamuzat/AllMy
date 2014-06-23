<?php
/**
 * A L{Connector} provides of L{twisted.internet.interfaces.IConnector} for
 * all POSIX-style reactors.
 * 
 * @ivar _addressType: the type returned by L{Connector.getDestination}.
 *      Either L{IPv4Address} or L{IPv6Address}, depending on the type of
 *       address.
 * @type _addressType: C{type}
 */
class Connector extends BaseConnector {
    //_addressType = address.IPv4Address

    public function __construct($host, $port, $factory, $timeout, $bindAddress, $reactor=Null) {
       /* if isinstance(port, _portNameType):
            try:
                port = socket.getservbyname(port, 'tcp')
            except socket.error as e:
            raise error.ServiceNameUnknownError(string="%s (%r)" % (e, port))*/
                $this->host = $host;
                $this->port = $post;
        /*if abstract.isIPv6Address(host):
            self._addressType = address.IPv6Address*/
        $this->bindAddress = $bindAddress
        parent::__construct($factory, $timeout, $reactor)
    }

    public function _makeTransport() {
        /**
        Create a L{Client} bound to this L{Connector}.

        @return: a new L{Client}
        @rtype: L{Client}
        */
        return Client($this->host, $this->port, $this->bindAddress, $this, $this->reactor);
    }

    public function getDestination() {
        /**
        @see: L{twisted.internet.interfaces.IConnector.getDestination}.
        */
     //   return self._addressType('TCP', self.host, self.port);
    }
}
