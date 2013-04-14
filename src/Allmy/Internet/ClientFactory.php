<?php
/**
 * A Protocol factory for clients.
 *
 *  This can be used together with the various connectXXX methods in
 *  reactors.
 */

abstract class ClientFactory implements Factory {
        /**Called when a connection has been started.
         * You can call connector.stopConnecting() to stop the connection attempt.
         * @param connector: a Connector object.
         */

    abstract public function startedConnecting($connector);
        /**
         * Called when a connection has failed to connect.
         * It may be useful to call connector.connect() - this will reconnect.
         * @type reason: L{twisted.python.failure.Failure}
         */

    abstract public function clientConnectionFailed($connector, $reason);
        /**Called when an established connection is lost.
         *
         *       It may be useful to call connector.connect() - this will reconnect.
         *
         *       @type reason: L{twisted.python.failure.Failure}
         */
    abstract public function clientConnectionLost($connector, $reason);
}
