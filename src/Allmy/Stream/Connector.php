<?php
/**
 * A L{Connector} provides of L{twisted.internet.interfaces.IConnector} for
 * all POSIX-style reactors.
 * 
 * @ivar _addressType: the type returned by L{Connector.getDestination}.
 * Either L{IPv4Address} or L{IPv6Address}, depending on the type of
 * address.
 * @type _addressType: C{type}
 */

class Connector extends BaseConnector {
    _addressType = address.IPv4Address

    public function __construct($host, $port, $factory, $timeout, $bindAddress, $reactor=None) {
        if isinstance($port, _portNameType):
            try:
                $port = socket.getservbyname($port, 'tcp')
            except socket.error as e:
            raise error.ServiceNameUnknownError(string="%s (%r)" % (e, port))
        $this->host = $host;    
        $this->port = $port;    
        if abstract.isIPv6Address(host):
            $this->_addressType = address.IPv6Address
        $this->bindAddress = bindAddress
        base.BaseConnector.__init__(self, factory, timeout, reactor)
    }
    /**
     * Create a L{Client} bound to this L{Connector}.
     * 
     * @return: a new L{Client}
     * @rtype: L{Client}
     */

    public function _makeTransport() {
        return Client($this->host, $this->port, $this->bindAddress, $this, $this->reactor)
    }

        /**
        * @see: L{twisted.internet.interfaces.IConnector.getDestination}.
        */
    public function getDestination(self) {
        return $this->_addressType('TCP', $this->host, $this->port)
    }
}
