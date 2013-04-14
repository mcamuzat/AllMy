<?php
namespace Allmy\Protocol;

/**
 * This is the base class for streaming connection-oriented protocols.
 * 
 * If you are going to write a new connection-oriented protocol for Twisted,
 * start here.  Any protocol implementation, either client or server, should
 * be a subclass of this class.
 * 
 * The API is quite simple.  Implement L{dataReceived} to handle both
 * event-based and synchronous input; output can be sent through the
 * 'transport' attribute, which is to be an instance that implements
 * L{twisted.internet.interfaces.ITransport}.  Override C{connectionLost} to be
 * notified when the connection ends.
 * 
 * Some subclasses exist already to help you write common types of protocols:
 * see the L{twisted.protocols.basic} module for a few of them.
 */
abstract class Protocol extends BaseProtocol implements IProtocol, ILoggingContext {
    /**
     * Return a prefix matching the class name, to identify log messages
     * related to this protocol instance.
     */
    public function logPrefix()
    {
        return __class__ ;
    }
    
    /**
     * Called whenever data is received.
     *
     * Use this method to translate to a higher-level message.  Usually, some
     * callback will be made upon the receipt of each complete protocol
     * message.
     *
     * @param data: a string of indeterminate length.  Please keep in mind
     * that you will probably need to buffer some data, as partial
     * (or multiple) protocol messages may be received!  I recommend
     * that unit tests for protocols call through to this method with
     * differing chunk sizes, down to one byte at a time.
     */

    public abstract function dataReceived($data);

    /**
     * Called when the connection is shut down.
     *
     * Clear any circular references here, and any external references
     * to this Protocol.  The connection has been closed.
     * 
     * @type reason: L{twisted.python.failure.Failure}
     */
    public function connectionLost($reason) {
    }


}
