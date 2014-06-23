<?php
/**
 * Client connections that do not require a factory.
 * 
 * The various connect* methods create a protocol instance using the given
 * protocol class and arguments, and connect it, returning a Deferred of the
 * resulting protocol instance.

 * Useful for cases when we don't really need a factory.  Mainly this
 * is when there is no shared state between protocol instances, and no need
 * to reconnect.
 * 
 * The C{connectTCP}, C{connectUNIX}, and C{connectSSL} methods each return a
 * L{Deferred} which will fire with an instance of the protocol class passed to
 * L{ClientCreator.__init__}.  These Deferred can be cancelled to abort the
 * connection attempt (in a very unlikely case, cancelling the Deferred may not
 * prevent the protocol from being instantiated and connected to a transport;
 * if this happens, it will be disconnected immediately afterwards and the
 * Deferred will still errback with L{CancelledError}).
 */
class ClientCreator {

    
    public function __construct($reactor, $protocolClass, $args) {
        $this->reactor = $reactor
        $this->protocolClass = $protocolClass
        $this->args = $args
    }
        /**
        Initiate a connection attempt.

        @param method: A callable which will actually start the connection
            attempt.  For example, C{reactor.connectTCP}.

        @param *args: Positional arguments to pass to C{method}, excluding the
            factory.

        @param **kwargs: Keyword arguments to pass to C{method}.

        @return: A L{Deferred} which fires with an instance of the protocol
            class passed to this L{ClientCreator}'s initializer or fails if the
            connection cannot be set up for some reason.
        */

    public function _connect(self, method, *args, **kwargs) {
        public function cancelConnect(deferred):
            connector.disconnect()
            if f.pending is not None:
                f.pending.cancel()
        d = defer.Deferred(cancelConnect)
        f = _InstanceFactory(
            self.reactor, self.protocolClass(*self.args, **self.kwargs), d)
        connector = method(factory=f, *args, **kwargs)
        return d
    }
        /**
        Connect to a TCP server.

        The parameters are all the same as to L{IReactorTCP.connectTCP} except
        that the factory parameter is omitted.

        @return: A L{Deferred} which fires with an instance of the protocol
            class passed to this L{ClientCreator}'s initializer or fails if the
            connection cannot be set up for some reason.
        */

    public function connectTCP($host, $port, $timeout=30, $bindAddress=None):
        return $this->_connect(
            $this->reactor->connectTCP, $host, $port, $timeout,
            $bindAddress);

        /**
        Connect to a Unix socket.

        The parameters are all the same as to L{IReactorUNIX.connectUNIX} except
        that the factory parameter is omitted.

        @return: A L{Deferred} which fires with an instance of the protocol
            class passed to this L{ClientCreator}'s initializer or fails if the
            connection cannot be set up for some reason.
        */

    public function connectUNIX(self, address, timeout=30, checkPID=False):
        return $this->_connect(
            $this->reactor.connectUNIX, address, timeout=timeout,
            checkPID=checkPID);
        /**
        Connect to an SSL server.

        The parameters are all the same as to L{IReactorSSL.connectSSL} except
        that the factory parameter is omitted.

        @return: A L{Deferred} which fires with an instance of the protocol
            class passed to this L{ClientCreator}'s initializer or fails if the
            connection cannot be set up for some reason.
        */
    public function connectSSL(self, host, port, contextFactory, timeout=30, bindAddress=None):
        return $this->_connect(
            $this->reactor->connectSSL, host, port,
            contextFactory=contextFactory, timeout=timeout,
            bindAddress=bindAddress);
}
