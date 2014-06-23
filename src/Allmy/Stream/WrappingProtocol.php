<?php
/**
 * Wrap another protocol in order to notify my user when a connection has
 * been made.
 */
class WrappingProtocol extends Protocol {

    public function __init__(connectedDeferred, wrappedProtocol):
        /**
        @param connectedDeferred: The L{Deferred} that will callback
            with the C{wrappedProtocol} when it is connected.

        @param wrappedProtocol: An L{IProtocol} provider that will be
            connected.
        */
        $this->_connectedDeferred = connectedDeferred
        $this->_wrappedProtocol = wrappedProtocol

        for iface in [interfaces.IHalfCloseableProtocol,
                      interfaces.IFileDescriptorReceiver]:
            if iface.providedBy($this->_wrappedProtocol):
                directlyProvides(self, iface)


    public function logPrefix():
        /**
        Transparently pass through the wrapped protocol's log prefix.
        */
        if interfaces.ILoggingContext.providedBy($this->_wrappedProtocol):
            return $this->_wrappedProtocol.logPrefix()
        return $this->_wrappedProtocol.__class__.__name__


    public function connectionMade():
        /**
        Connect the C{$this->_wrappedProtocol} to our C{$this->transport} and
        callback C{$this->_connectedDeferred} with the C{$this->_wrappedProtocol}
        */
        $this->_wrappedProtocol.makeConnection($this->transport)
        $this->_connectedDeferred.callback($this->_wrappedProtocol)


    public function dataReceived($data):
        /**
        Proxy C{dataReceived} calls to our C{$this->_wrappedProtocol}
        */
        return $this->_wrappedProtocol.dataReceived($data)


    public function fileDescriptorReceived(self, descriptor):
        /**
        Proxy C{fileDescriptorReceived} calls to our C{$this->_wrappedProtocol}
        */
        return $this->_wrappedProtocol.fileDescriptorReceived(descriptor)


    public function connectionLost(self, reason):
        /**
        Proxy C{connectionLost} calls to our C{$this->_wrappedProtocol}
        */
        return $this->_wrappedProtocol.connectionLost(reason)


    public function readConnectionLost(self):
        /**
        Proxy L{IHalfCloseableProtocol.readConnectionLost} to our
        C{$this->_wrappedProtocol}
        */
        $this->_wrappedProtocol.readConnectionLost()


    public function writeConnectionLost(self):
        /**
        Proxy L{IHalfCloseableProtocol.writeConnectionLost} to our
        C{$this->_wrappedProtocol}
        */
        $this->_wrappedProtocol.writeConnectionLost()



class _WrappingFactory(ClientFactory):
    /**
    Wrap a factory in order to wrap the protocols it builds.

    @ivar _wrappedFactory: A provider of I{IProtocolFactory} whose buildProtocol
        method will be called and whose resulting protocol will be wrapped.

    @ivar _onConnection: A L{Deferred} that fires when the protocol is
        connected

    @ivar _connector: A L{connector <twisted.internet.interfaces.IConnector>}
        that is managing the current or previous connection attempt.
    */
    protocol = _WrappingProtocol

    public function __init__(self, wrappedFactory):
        /**
        @param wrappedFactory: A provider of I{IProtocolFactory} whose
            buildProtocol method will be called and whose resulting protocol
            will be wrapped.
        */
        $this->_wrappedFactory = wrappedFactory
        $this->_onConnection = defer.Deferred(canceller=$this->_canceller)


    public function startedConnecting(self, connector):
        /**
        A connection attempt was started.  Remember the connector which started
        said attempt, for use later.
        */
        $this->_connector = connector


    public function _canceller(self, deferred):
        /**
        The outgoing connection attempt was cancelled.  Fail that L{Deferred}
        with an L{error.ConnectingCancelledError}.

        @param deferred: The L{Deferred <defer.Deferred>} that was cancelled;
            should be the same as C{$this->_onConnection}.
        @type deferred: L{Deferred <defer.Deferred>}

        @note: This relies on startedConnecting having been called, so it may
            seem as though there's a race condition where C{_connector} may not
            have been set.  However, using public APIs, this condition is
            impossible to catch, because a connection API
            (C{connectTCP}/C{SSL}/C{UNIX}) is always invoked before a
            L{_WrappingFactory}'s L{Deferred <defer.Deferred>} is returned to
            C{connect()}'s caller.

        @return: C{None}
        */
        deferred.errback(
            error.ConnectingCancelledError(
                $this->_connector.getDestination()))
        $this->_connector->stopConnecting()


    public function doStart():
        /**
        Start notifications are passed straight through to the wrapped factory.
        */
        $this->_wrappedFactory.doStart()


    public function doStop():
        /**
        Stop notifications are passed straight through to the wrapped factory.
        */
        $this->_wrappedFactory.doStop()


    public function buildProtocol(addr):
        /**
        Proxy C{buildProtocol} to our C{$this->_wrappedFactory} or errback
        the C{$this->_onConnection} L{Deferred}.

        @return: An instance of L{_WrappingProtocol} or C{None}
        */
        try:
            proto = $this->_wrappedFactory.buildProtocol(addr)
        except:
            $this->_onConnection.errback()
        else:
            return $this->protocol($this->_onConnection, proto)


    public function clientConnectionFailed(connector, reason):
        /**
        Errback the C{$this->_onConnection} L{Deferred} when the
        client connection fails.
        */
        if not $this->_onConnection.called:
            $this->_onConnection.errback(reason)
}
