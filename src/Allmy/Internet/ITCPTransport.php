<?php
    /**
    A TCP based transport.
     */

interface ITCPTransport implements ITransport {
        /**
        Half-close the write side of a TCP connection.

        If the protocol instance this is attached to provides
        IHalfCloseableProtocol, it will get notified when the operation is
        done. When closing write connection, as with loseConnection this will
        only happen when buffer has emptied and there is no registered
        producer.
	 */

    public function loseWriteConnection();
        /**
        Close the connection abruptly.

        Discards any buffered data, stops any registered producer,
        and, if possible, notifies the other end of the unclean
        closure.

        @since; 11.1
	 */


    public function abortConnection();

        /**
        Return if C{TCP_NODELAY} is enabled.
	 */

    public function getTcpNoDelay();
        /**
        Enable/disable C{TCP_NODELAY}.

        Enabling C{TCP_NODELAY} turns off Nagle's algorithm. Small packets are
        sent sooner, possibly at the expense of overall throughput.
	 */

    public function setTcpNoDelay(enabled);
        /**
        Return if C{SO_KEEPALIVE} is enabled.
	 */


    public function getTcpKeepAlive();
        /**
        Enable/disable C{SO_KEEPALIVE}.

        Enabling C{SO_KEEPALIVE} sends packets periodically when the connection
        is otherwise idle, usually once every two hours. They are intended
        to allow detection of lost peers in a non-infinite amount of time.
	 */

    public function setTcpKeepAlive(enabled);
        /**
        Returns L{IPv4Address} or L{IPv6Address}.
	 */


    public function getHost();
        /**
        Returns L{IPv4Address} or L{IPv6Address}.
	 */

    public function getPeer();
}
