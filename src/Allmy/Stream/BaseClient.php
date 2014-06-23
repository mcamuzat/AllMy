<?php
    /**
    A base class for client TCP (and similiar) sockets.

    @ivar realAddress: The address object that will be used for socket.connect;
        this address is an address tuple (the number of elements dependent upon
        the address family) which does not contain any names which need to be
        resolved.
    @type realAddress: C{tuple}

    @ivar _base: L{Connection}, which is the base class of this class which has
        all of the useful file descriptor methods.  This is used by
        L{_TLSServerMixin} to call the right methods to directly manipulate the
        transport, as is necessary for writing TLS-encrypted bytes (whereas
        those methods on L{Server} will go through another layer of TLS if it
        has been enabled).
    */
class BaseClient extends Connection {
use TBaseBaseClient,TTLSClientMixin;
    _base = Connection
    _commonConnection = Connection

        /**
        Implement the POSIX-ish (i.e.
        L{twisted.internet.interfaces.IReactorFDSet}) method of detaching this
        socket from the reactor for L{_BaseBaseClient}.
        */
    def _stopReadingAndWriting(self):
        if hasattr(self, "reactor"):
            # this doesn't happen if we failed in __init__
            self.stopReading()
            self.stopWriting()


        /**
        Clean up references to the socket and its file descriptor.

        @see: L{_BaseBaseClient}
        */
    def _collectSocketDetails(self):
        del self.socket, self.fileno


        /**(internal) Create a non-blocking socket using
        self.addressFamily, self.socketType.
        */
    def createInternetSocket(self):
        s = socket.socket(self.addressFamily, self.socketType)
        s.setblocking(0)
        fdesc._setCloseOnExec(s.fileno())
        return s


        /**
        Initiate the outgoing connection attempt.

        @note: Applications do not need to call this method; it will be invoked
            internally as part of L{IReactorTCP.connectTCP}.
        */
    def doConnect(self):
        self.doWrite = self.doConnect
        self.doRead = self.doConnect
        if not hasattr(self, "connector"):
            # this happens when connection failed but doConnect
            # was scheduled via a callLater in self._finishInit
            return

        err = self.socket.getsockopt(socket.SOL_SOCKET, socket.SO_ERROR)
        if err:
            self.failIfNotConnected(error.getConnectError((err, strerror(err))))
            return

        # doConnect gets called twice.  The first time we actually need to
        # start the connection attempt.  The second time we don't really
        # want to (SO_ERROR above will have taken care of any errors, and if
        # it reported none, the mere fact that doConnect was called again is
        # sufficient to indicate that the connection has succeeded), but it
        # is not /particularly/ detrimental to do so.  This should get
        # cleaned up some day, though.
        try:
            connectResult = self.socket.connect_ex(self.realAddress)
        except socket.error as se:
            connectResult = se.args[0]
        if connectResult:
            if connectResult == EISCONN:
                pass
            # on Windows EINVAL means sometimes that we should keep trying:
            # http://msdn.microsoft.com/library/default.asp?url=/library/en-us/winsock/winsock/connect_2.asp
            elif ((connectResult in (EWOULDBLOCK, EINPROGRESS, EALREADY)) or
                  (connectResult == EINVAL and platformType == "win32")):
                self.startReading()
                self.startWriting()
                return
            else:
                self.failIfNotConnected(error.getConnectError((connectResult, strerror(connectResult))))
                return

        # If I have reached this point without raising or returning, that means
        # that the socket is connected.
        del self.doWrite
        del self.doRead
        # we first stop and then start, to reset any references to the old doRead
        self.stopReading()
        self.stopWriting()
        self._connectDone()


        /**
        This is a hook for when a connection attempt has succeeded.

        Here, we build the protocol from the
        L{twisted.internet.protocol.ClientFactory} that was passed in, compute
        a log string, begin reading so as to send traffic to the newly built
        protocol, and finally hook up the protocol itself.

        This hook is overridden by L{ssl.Client} to initiate the TLS protocol.
        */
    def _connectDone(self):
        self.protocol = self.connector.buildProtocol(self.getPeer())
        self.connected = 1
        logPrefix = self._getLogPrefix(self.protocol)
        self.logstr = "%s,client" % logPrefix
        self.startReading()
        self.protocol.makeConnection(self)



_NUMERIC_ONLY = socket.AI_NUMERICHOST | _AI_NUMERICSERV

    /**
    Resolve an IPv6 literal into an IPv6 address.

    This is necessary to resolve any embedded scope identifiers to the relevant
    C{sin6_scope_id} for use with C{socket.connect()}, C{socket.listen()}, or
    C{socket.bind()}; see U{RFC 3493 <https://tools.ietf.org/html/rfc3493>} for
    more information.

    @param ip: An IPv6 address literal.
    @type ip: C{str}

    @param port: A port number.
    @type port: C{int}

    @return: a 4-tuple of C{(host, port, flow, scope)}, suitable for use as an
        IPv6 address.

    @raise socket.gaierror: if either the IP or port is not numeric as it
        should be.
    */
def _resolveIPv6(ip, port):
    return socket.getaddrinfo(ip, port, 0, 0, 0, _NUMERIC_ONLY)[0][4]
}
