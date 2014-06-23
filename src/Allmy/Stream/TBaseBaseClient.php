<?php
/**
* Code shared with other (non-POSIX) reactors for management of general
* outgoing connections.
* 
* Requirements upon subclasses are documented as instance variables rather
* than abstract methods, in order to avoid MRO confusion, since this base is
* mixed in to unfortunately weird and distinctive multiple-inheritance
* hierarchies and many of these attributes are provided by peer classes
* rather than descendant classes in those hierarchies.
* 
* @ivar addressFamily: The address family constant (C{socket.AF_INET},
*    C{socket.AF_INET6}, C{socket.AF_UNIX}) of the underlying socket of this
*    client connection.
* @type addressFamily: C{int}
* 
* @ivar socketType: The socket type constant (C{socket.SOCK_STREAM} or
*     C{socket.SOCK_DGRAM}) of the underlying socket.
* @type socketType: C{int}
* 
* @ivar _requiresResolution: A flag indicating whether the address of this
*     client will require name resolution.  C{True} if the hostname of said
    * address indicates a name that must be resolved by hostname lookup,
    * C{False} if it indicates an IP address literal.
* @type _requiresResolution: C{bool}
* 
* @cvar _commonConnection: Subclasses must provide this attribute, which
    * indicates the L{Connection}-alike class to invoke C{__init__} and
    * C{connectionLost} on.
* @type _commonConnection: C{type}
* 
* @ivar _stopReadingAndWriting: Subclasses must implement in order to remove
    * this transport from its reactor's notifications in response to a
    * terminated connection attempt.
* @type _stopReadingAndWriting: 0-argument callable returning C{None}
* 
* @ivar _closeSocket: Subclasses must implement in order to close the socket
    * in response to a terminated connection attempt.
* @type _closeSocket: 1-argument callable; see L{_SocketCloser._closeSocket}
* 
* @ivar _collectSocketDetails: Clean up references to the attached socket in
    * its underlying OS resource (such as a file descriptor or file handle),
    * as part of post connection-failure cleanup.
* @type _collectSocketDetails: 0-argument callable returning C{None}.
* 
* @ivar reactor: The class pointed to by C{_commonConnection} should set this
    * attribute in its constructor.
* @type reactor: L{twisted.internet.interfaces.IReactorTime},
    * L{twisted.internet.interfaces.IReactorCore},
    * L{twisted.internet.interfaces.IReactorFDSet}
*/
trait TBaseBaseClient {

    $addressFamily = AF_INET;
    $socketType = SOCK_STREAM;

        /**
        Called by subclasses to continue to the stage of initialization where
        the socket connect attempt is made.

        @param whenDone: A 0-argument callable to invoke once the connection is
            set up.  This is C{None} if the connection could not be prepared
            due to a previous error.

        @param skt: The socket object to use to perform the connection.
        @type skt: C{socket._socketobject}

        @param error: The error to fail the connection with.

        @param reactor: The reactor to use for this client.
        @type reactor: L{twisted.internet.interfaces.IReactorTime}
        */
    public function _finishInit($whenDone, $skt, $error, $reactor):
        if ($whenDone) {
            $this->_commonConnection.__init__(self, $skt, $None, $reactor)
                $reactor.callLater(0, $whenDone)
        } else {
            $reactor.callLater(0, $this->failIfNotConnected, error);
        }

        /**
        Resolve the name that was passed to this L{_BaseBaseClient}, if
        necessary, and then move on to attempting the connection once an
        address has been determined.  (The connection will be attempted
        immediately within this function if either name resolution can be
        synchronous or the address was an IP address literal.)

        @note: You don't want to call this method from outside, as it won't do
            anything useful; it's just part of the connection bootstrapping
            process.  Also, although this method is on L{_BaseBaseClient} for
            historical reasons, it's not used anywhere except for L{Client}
            itself.

        @return: C{None}
        */
    public function resolveAddress() {
        if $this->_requiresResolution:
            d = $this->reactor.resolve($this->addr[0])
            d.addCallback(lambda n: (n,) + $this->addr[1:])
            d.addCallbacks($this->_setRealAddress, $this->failIfNotConnected)
        else:
            $this->_setRealAddress($this->addr)

    }
        /**
        Set the resolved address of this L{_BaseBaseClient} and initiate the
        connection attempt.

        @param address: Depending on whether this is an IPv4 or IPv6 connection
            attempt, a 2-tuple of C{(host, port)} or a 4-tuple of C{(host,
            port, flow, scope)}.  At this point it is a fully resolved address,
            and the 'host' portion will always be an IP address, not a DNS
            name.
        */
    public function _setRealAddress($address) {
        $this->realAddress = address
        $this->doConnect()
    }

        /**
        Generic method called when the attemps to connect failed. It basically
        cleans everything it can: call connectionFailed, stop read and write,
        delete socket related members.
        */
    public function failIfNotConnected($err) {
        if ($this->connected or $this->disconnected or
            not hasattr(self, "connector")):
            return

        $this->_stopReadingAndWriting()
        try:
            $this->_closeSocket(True)
        except AttributeError:
            pass
        else:
            $this->_collectSocketDetails()
        $this->connector.connectionFailed(failure.Failure(err))
        del $this->connector

    }
        /**
        If a connection attempt is still outstanding (i.e.  no connection is
        yet established), immediately stop attempting to connect.
        */
    public function stopConnecting() {
        $this->failIfNotConnected(error.UserError())

    }
        /**
        Invoked by lower-level logic when it's time to clean the socket up.
        Depending on the state of the connection, either inform the attached
        L{Connector} that the connection attempt has failed, or inform the
        connected L{IProtocol} that the established connection has been lost.

        @param reason: the reason that the connection was terminated
        @type reason: L{Failure}
        */
    public function connectionLost($reason) {
        if not $this->connected:
            $this->failIfNotConnected(error.ConnectError(string=reason))
        else:
            $this->_commonConnection.connectionLost(self, reason)
            $this->connector.connectionLost(reason)
    }
}
