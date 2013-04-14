<?php

namespace Allmy\TCP;
/**
 * I am a transport for bytes.
 *
 * I represent (and wrap) the physical connection and synchronicity
 * of the framework which is talking to the network.  I make no
 * representations about whether calls to me will happen immediately
 * or require returning to a control loop, or whether they will happen
 * in the same or another thread.  Consider methods of this class
 * (aside from getPeer) to be 'thrown over the wall', to happen at some
 * indeterminate time.
 */
interface ITransport {
    /**
     * Write some data to the physical connection, in sequence, in a
     * non-blocking fashion.
     * 
     * If possible, make sure that it is all written.  No data will
     * ever be lost, although (obviously) the connection may be closed
     * before it all gets through.
     */
   
    public function write($data);
    /**
     * Write a list of strings to the physical connection.
     * 
     * If possible, make sure that all of the data is written to
     * the socket at once, without first copying it all into a
     * single string.
     */

	public function writeSequence($data);
    /**
     * Close my connection, after writing all pending data.
     * 
     * Note that if there is a registered producer on a transport it
     * will not be closed until the producer has been unregistered.
     */

    public function loseConnection();

	 /**
      * Get the remote address of this connection.
      * 
      * Treat this method with caution.  It is the unfortunate result of the
      * CGI and Jabber standards, but should not be considered reliable for
      * the usual host of reasons; port forwarding, proxying, firewalls, IP
      * masquerading, etc.
      * 
      * @return: An L{IAddress} provider.
      */
    public function getPeer();

    /**
     * Similar to getPeer, but returns an address describing this side of the
     * connection.
     *
     *	@return: An L{IAddress} provider.
     */
	public function getHost();

}
