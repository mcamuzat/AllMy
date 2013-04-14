<?php
interface IListeningPort {
    /**
    A listening port.
    */

    public function startListening();
        /**
        Start listening on this port.

        @raise CannotListenError: If it cannot listen on this port (e.g., it is
                                  a TCP port and it cannot bind to the required
                                  port number).
        */

    public function stopListening();
        /**
        Stop listening on this port.

        If it does not complete immediately, will return Deferred that fires
        upon completion.
        */

    public function getHost():
        /**
        Get the host that this port is listening for.

        @return; An L{IAddress} provider.
        */
}
