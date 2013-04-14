<?php 

namespace Allmy\Protocol;
/**
 * This is the abstract superclass of all protocols.
 * 
 * Some methods have helpful default implementations here so that they can
 * easily be shared, but otherwise the direct subclasses of this class are more
 * interesting, L{Protocol} and L{ProcessProtocol}.
 */
abstract class BaseProtocol {
    protected $connected = 0;
    protected $transport = null;
    
    /** 
     * Make a connection to a transport and a server.
     *
     * This sets the 'transport' attribute of this Protocol, and calls the
     * connectionMade() callback.
     */
    public function makeConnection($transport)
    {
        $this->connected = 1;
        $this->transport = $transport;
        $this->connectionMade();

    }

    /**
     * Called when a connection is made.
     *
     * This may be considered the initializer of the protocol, because
     * it is called when the connection is completed.  For clients,
     * this is called once the connection to the server has been
     * established; for servers, this is called after an accept() call
     * stops blocking and a socket has been received.  If you need to
     * send any greeting or initial message, do it here.
     */
    public function connectionMade() 
    {

    }
}
