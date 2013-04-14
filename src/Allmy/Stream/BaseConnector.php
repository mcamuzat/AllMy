<?php
    /**Basic implementation of connector.
     *
     * State can be: "connecting", "connected", "disconnected"
     */
class BaseConnector implements IConnector {
    protected state = null;
    protected timeoutID = null; 
    protected factoryStarted = 0;

    const CONNECTING = 'connecting';
    const CONNECTED = 'connected';
    const DISCONNECTED = 'disconnected';

    public function __construct ($factory, $timeout, $reactor) {
        $this->state = DISCONNECTED;
        $this->reactor = $reactor;
        $this->factory = $factory;
        $this->timeout = $timeout;
    }
        /**Disconnect whatever our state is.*/
    public function disconnect() {
        if ($this->state == CONNECTING) {
            $this->stopConnecting();
        }
        elseif ($this->state == CONNECTED) {
            $this->transport->loseConnection();
        }

        /**Start connection to remote server.*/
    public function connect() {
        if ($this->state != DISCONNECTED) {
            throw RuntimeError("can't connect in this state");
        }
        
        $this->state = CONNECTING;
        if (!$this->factoryStarted) {
            $this->factory->doStart();
            $this->factoryStarted = 1;
        }
       
        $this->transport = $this->_makeTransport()
        if (!$this->timeout) {
            $this->timeoutID = $this->reactor.callLater($this->timeout, transport.failIfNotConnected, error.TimeoutError())
        }
        
        $this->factory->startedConnecting($this);
    }
    public function stopConnecting() {
        /**Stop attempting to connect.*/
        if $this->state != "connecting":
            raise error.NotConnectingError("we're not trying to connect")
        $this->state = "disconnected"
        $this->transport.failIfNotConnected(error.UserError())
        del $this->transport
    }
    public function cancelTimeout() {
        if $this->timeoutID is not None:
            try:
                $this->timeoutID.cancel()
            except ValueError:
                pass
            del $this->timeoutID

    }
    public function buildProtocol(self, addr) {
        $this->state = "connected"
        $this->cancelTimeout()
        return $this->factory.buildProtocol(addr)
    }
    public function connectionFailed(self, reason):
        $this->cancelTimeout()
        $this->transport = None
        $this->state = "disconnected"
        $this->factory->clientConnectionFailed(self, reason)
        if $this->state == "disconnected":
            # factory hasn't called our connect() method
            $this->factory.doStop()
            $this->factoryStarted = 0

    public function connectionLost(self, reason):
        $this->state = "disconnected"
        $this->factory.clientConnectionLost(self, reason)
        if $this->state == "disconnected":
            # factory hasn't called our connect() method
            $this->factory.doStop()
            $this->factoryStarted = 0

    public function getDestination(self):
        raise NotImplementedError(
            reflect.qual($this->__class__) + " did not implement "
            "getDestination")
}
