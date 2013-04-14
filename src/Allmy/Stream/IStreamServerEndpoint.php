<?php
/**
 * A stream server endpoint is a place that a L{Factory} can listen for
 * incoming connections.
 * 
 * @since: 10.1
 */
interface IStreamServerEndpoint {
        /**
         * Listen with C{protocolFactory} at the location specified by this
         * L{IStreamServerEndpoint} provider.
         * 
         * @param protocolFactory: A provider of L{IProtocolFactory}
         * @return: A L{Deferred} that results in an L{IListeningPort} or an
         * L{CannotListenError}
         */

    public function listen(IProtocolFactory $protocolFactory):
}
