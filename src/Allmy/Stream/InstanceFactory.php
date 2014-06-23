<?php 
    /**
    * Factory used by ClientCreator.
*
    * @ivar deferred: The L{Deferred} which represents this connection attempt and
    *   which will be fired when it succeeds or fails.
*
    * @ivar pending: After a connection attempt succeeds or fails, a delayed call
    *   which will fire the L{Deferred} representing this connection attempt.
    */

class _InstanceFactory extends ClientFactory {

    public $noisy = False;
    public $pending = Null;

    public function __construct($reactor, $instance, $deferred) {
        $this->reactor = $reactor;
        $this->instance = $instance;
        $this->deferred = $deferred;
    }

    public function __toString() {
        return "<ClientCreator factory: %r>" % (self.instance, )
    }

        /**
        Return the pre-constructed protocol instance and arrange to fire the
        waiting L{Deferred} to indicate success establishing the connection.
        */
    public function buildProtocol(self, addr) {
        $this->pending = $this->reactor->callLater(
            0, $this->fire, $this->deferred->callback, $this->instance);
        $this->deferred = null;
        return $this->instance;
    }

        /**
        Arrange to fire the waiting L{Deferred} with the given failure to
        indicate the connection could not be established.
        */
    public function clientConnectionFailed($connector, $reason) {
        $this->pending = $this->reactor->callLater(
            0, $this->fire(this->deferred->reject, $reason));
        $this->deferred = Null;
    }
        /**
        * Clear C{self.pending} to avoid a reference cycle and then invoke func
        * with the value.
        */

    public function fire($func, $value) {
        $this->pending = Null;
        $func($value)
    }
}
