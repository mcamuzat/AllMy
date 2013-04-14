<?php

namespace Allmy\Tcp;

use Allmy\Stream\TAbortingMixin;
use Allmy\Stream\Server;
/**
 * A TCP server port, listening for connections.
 * 
 * When a connection is accepted, this will call a factory's buildProtocol
 * with the incoming address as an argument, according to the specification
 * described in L{twisted.internet.interfaces.IProtocolFactory}.
 * 
 * If you wish to change the sort of transport that will be used, the
 * C{transport} attribute will be called with the signature expected for
 * C{Server.__init__}, so it can be replaced.
 * 
 * @ivar deferred: a deferred created when L{stopListening} is called, and
 * that will fire when connection is lost. This is not to be used it
 * directly: prefer the deferred returned by L{stopListening} instead.
 * @type deferred: L{defer.Deferred}

 * @ivar disconnecting: flag indicating that the L{stopListening} method has
 * been called and that no connections should be accepted anymore.
 * @type disconnecting: C{bool}
 * 
 * @ivar connected: flag set once the listen has successfully been called on
 * the socket.
 * @type connected: C{bool}
 * 
 * @ivar _type: A string describing the connections which will be created by
 * this port.  Normally this is C{"TCP"}, since this is a TCP port, but
 * when the TLS implementation re-uses this class it overrides the value
 * with C{"TLS"}.  Only used for logging.
 * 
 * @ivar _preexistingSocket: If not C{None}, a L{socket.socket} instance which
 * was created and initialized outside of the reactor and will be used to
 * listen for connections (instead of a new socket being created by this
 * L{Port}).
 */

class Port extends BasePort {
    use TAbortingMixin;

    protected $socketType = SOCK_STREAM;
    
    protected $transport = 'Server';
    protected $reactor = null;
    protected $sessionno = 0;
    protected $interface = '';
    protected $backlog = 50;

    protected $_type = 'TCP';

    // Actual port number being listened on, only set to a non-None
    // value when we are actually listening.
    protected $_realPortNumber = null;

    // An externally initialized socket that we will use, rather than creating
    // our own.
    protected $_preexistingSocket = null;

    protected $addressFamily = AF_INET;
    //protected $_addressType = address.IPv4Address;

     /** 
      * Initialize with a numeric port to listen on.
      */
    public function __construct($port, $factory, $backlog=50, $interface='127.0.0.1', $reactor=null) {
        $this->reactor = $reactor; 
        $this->port = $port;
        $this->factory = $factory;
        $this->backlog = $backlog;
        $this->interface = $interface;
    }

    /**
     * Create and bind my socket, and begin listening on it.
     *
     * This is called on unserialization, and must be called after creating a
     * server to begin listening on the specified port.
     */
    public function startListening()
    {
        if (!$this->_preexistingSocket) {
            $socket = @stream_socket_server("tcp://$this->interface:$this->port", $errno, $errstr);
        }

        if (false === $socket) {
            $message = "Could not bind to tcp://$$this->interface:$this->port: $errstr";
            throw new \Exception($message);
        }
        
        $this->factory->doStart();
        $this->connected = True;
        $this->socket = $socket;

        $this->fileno = (int)$this->socket;
        $this->numberAccepts = 100;
        $this->startReading();
       

    }

    /*    if self._preexistingSocket is None:
            # Create a new socket and make it listen
            try:
                skt = self.createInternetSocket()
                if self.addressFamily == socket.AF_INET6:
                    addr = _resolveIPv6(self.interface, self.port)
                else:
                    addr = (self.interface, self.port)
                skt.bind(addr)
            except socket.error as le:
                raise CannotListenError(self.interface, self.port, le)
            skt.listen(self.backlog)
        else:
            # Re-use the externally specified socket
            skt = self._preexistingSocket
            self._preexistingSocket = None
            # Avoid shutting it down at the end.
            self._socketShutdownMethod = None

        # Make sure that if we listened on port 0, we update that to
        # reflect what the OS actually assigned us.
        self._realPortNumber = skt.getsockname()[1]

        log.msg("%s starting on %s" % (
                self._getLogPrefix(self.factory), self._realPortNumber))

        # The order of the next 5 lines is kind of bizarre.  If no one
        # can explain it, perhaps we should re-arrange them.

    }
     */
    public function startReading()
    {
        $this->reactor->addReader($this);
    }

   /**Called when my socket is ready for reading.
    * This accepts a connection and calls self.protocol() to handle the
   * wire-level protocol.
   */
    public function doRead() {
        try {
            $newSocket =  stream_socket_accept($this->socket);
            $protocol = $this->factory->buildProtocol('null');
            $session = $this->sessionno;// $this->sessionno++ is ok
            $this->sessionno++; 
            $transport = new Server($newSocket, $protocol, '127.0.0.1',$this, $session, $this->reactor);
            $protocol->makeConnection($transport);
        } catch (Exception $e) {


        }
        
     /*   try:
               skt, addr = self.socket.accept()
                           break
                    raise

                fdesc._setCloseOnExec(skt.fileno())
                protocol = self.factory.buildProtocol(self._buildAddr(addr))
                if protocol is None:
                    skt.close()
                    continue
                s = self.sessionno
                self.sessionno = s+1
                transport = self.transport(skt, protocol, addr, self, s, self.reactor)
                protocol.makeConnection(transport)
            else:
                self.numberAccepts = self.numberAccepts+20
        except:
            # Note that in TLS mode, this will possibly catch SSL.Errors
            # raised by self.socket.accept()
            #
            # There is no "except SSL.Error:" above because SSL may be
            # None if there is no SSL support.  In any case, all the
            # "except SSL.Error:" suite would probably do is log.deferr()
            # and return, so handling it here works just as well.
        log.deferr()*/
    }

    public function handleConnection($socket)
    {
        stream_set_blocking($socket, 0);

        $client = $this->createConnection($socket);

        $this->emit('connection', array($client));
    }

    public function getPort()
    {
        $name = stream_socket_get_name($this->master, false);

        return (int) substr(strrchr($name, ':'), 1);
    }

    public function shutdown()
    {
        $this->reactor->removeStream($this->master);
        fclose($this->master);
    }

    public function createConnection($socket)
    {
        return new Connection($socket, $this->reactor);
    }
}
