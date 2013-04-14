<?php 

namespace Allmy\Protocol;
/**
 * This is a factory which produces protocols.
 * 
 * By default, buildProtocol will create a protocol of the class given in
 * $this->protocol.
 */

abstract class ProtocolFactory implements IProtocolFactory {
    /**Create an instance of a subclass of Protocol.
     * 
     * The returned instance will handle input on an incoming server
     * connection, and an attribute \"factory\" pointing to the creating
     * factory.
     * 
     * Override this method to alter how Protocol instances get created.
     * 
     * @param addr: an object implementing L{twisted.internet.interfaces.IAddress}
     */

    abstract public function buildProtocol($addr);

    # put a subclass of Protocol here:
    protected $protocol = null;

    protected $numPorts = 0;
    protected $noisy = True;

    /**
     * Describe this factory for log messages.
     */
    public function logPrefix() {
        return __class__;
    }


    /**Make sure startFactory is called.
     * 
     *  Users should not call this function themselves!
     */
    public  function doStart() {
        if ($this->numPorts == 0) {
            if ($this->noisy) {
                echo("Starting factory");
                $this->startFactory();
            }
        }
        $this->numPorts = $this->numPorts + 1;
    }

    /**Make sure stopFactory is called.
     * 
     Users should not call this function themselves!
     */
    public function doStop() {
        if ($this->numPorts == 0) {
            # this shouldn't happen, but does sometimes and this is better
            # than blowing up in assert as we did previously.
            return;
        }

        $this->numPorts = $this->numPorts - 1;

        if (!$this->numPorts) {
            if ($this->noisy)  {
                log.msg("Stopping factory %r" % self);
                    $this->stopFactory();
            }
        }
    }
    /**This will be called before I begin listening on a Port or Connector.
     * 
     * It will only be called once, even if the factory is connected
     * to multiple ports.

     * This can be used to perform 'unserialization' tasks that
     * are best put off until things are actually running, such
     * as connecting to a database, opening files, etcetera.
     */
    public function startFactory() {

    }

        /**This will be called before I stop listening on all Ports/Connectors.

        * This can be overridden to perform 'shutdown' tasks such as disconnecting
        * database connections, closing files, etc.
* 
        * It will be called, for example, before an application shuts down,
        * if it was connected to a port. User code should not call this function
        * directly.
         */
    public function  stopFactory() {
    }

 }
