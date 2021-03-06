<?php 

interface IProtocol  {
    
    
    /**
     * Called whenever data is received.
     *
     * Use this method to translate to a higher-level message.  Usually, some
     * callback will be made upon the receipt of each complete protocol
     * message.
     *
     * @param data: a string of indeterminate length.  Please keep in mind
     *  that you will probably need to buffer some data, as partial
     *  (or multiple) protocol messages may be received!  I recommend
     *  that unit tests for protocols call through to this method with
     *  differing chunk sizes, down to one byte at a time.
     */

    public function dataReceived($data);
    /**
     *  Called when the connection is shut down.
     *
     *       Clear any circular references here, and any external references
     *      to this Protocol.  The connection has been closed. The C{reason}
     *     Failure wraps a L{twisted.internet.error.ConnectionDone} or
     *    L{twisted.internet.error.ConnectionLost} instance (or a subclass
     *   of one of those).
     *
     *       @type reason: L{twisted.python.failure.Failure}
     */

    public function connectionLost($reason);

    /**
     *    Make a connection to a transport and a server.
     */
    public function makeConnection($transport);
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
    public function connectionMade();
}

