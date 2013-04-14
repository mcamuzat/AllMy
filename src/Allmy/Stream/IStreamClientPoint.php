<?php
/**
 * A stream client endpoint is a place that L{ClientFactory} can connect to.
 * For example, a remote TCP host/port pair would be a TCP client endpoint.
 * 
 * @since: 10.1
 */
interface IStreamClientEndpoint {

    /**
     * Connect the C{protocolFactory} to the location specified by this
     * L{IStreamClientEndpoint} provider.
     * 
     * @param protocolFactory: A provider of L{IProtocolFactory}
     * @return: A L{Deferred} that results in an L{IProtocol} upon successful
     *   connection otherwise a L{ConnectError}
     */
    public function connect(IProtocolFactory $protocolFactory)
}
