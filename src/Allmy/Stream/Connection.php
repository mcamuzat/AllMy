<?php 

namespace Allmy\Stream;

use Allmy\Tcp\ITCPTransport ;
/**
 * Superclass of all socket-based FileDescriptors.
 *
 * This is an abstract superclass of all objects which represent a TCP/IP
 * connection based socket.
 * 
 * @ivar logstr: prefix used when logging events related to this connection.
 * @type logstr: C{str}
 */
abstract class Connection extends FileDescriptor implements ITCPTransport, ISystemHandle 
{
    use TSocketCloser, TAbortingMixin;

    public function __construct($skt, $protocol, $reactor=null) {
        parent::__construct($reactor);
        $this->socket = $skt;
        stream_set_blocking($skt,0);
        $this->fileno = (int)$skt;
        $this->protocol = $protocol;
    }

    /** 
     * Return the socket for this connection.
     */
    public function getHandle() {
        return $this->socket;
    }

    /**
     * Calls $this->protocol.dataReceived with all available data.
     *
     * This reads up to $this->bufferSize bytes of data from its socket, then
     * calls $this->dataReceived(data) to process it.  If the connection is not
     * lost through an error in the physical recv(), this function will return
     * the result of the dataReceived call.
     */
    public function doRead() {
    //    try:
            $data = stream_socket_recvfrom($this->socket, 1024, 0 /*$this->bufferSize*/);
      //  except socket.error as se:
       //     if se.args[0] == EWOULDBLOCK:
         //       return
          //  else:
            //    return main.CONNECTION_LOST
        return $this->_dataReceived($data);

    }
    
    public function _dataReceived($data){
     //   if not data:
     //       return main.CONNECTION_DONE
         $this->protocol->dataReceived($data);
    }

    /**
     * Write as much as possible of the given data to this TCP connection.
     *
     * This sends up to C{$this->SEND_LIMIT} bytes from C{data}.  If the
     * connection is lost, an exception is returned.  Otherwise, the number
     * of bytes successfully written is returned.
     */
    public function writeSomeData($data)
    {
        stream_socket_sendto($this->socket, $data);
        return strlen($data);

        # Limit length of buffer to try to send, because some OSes are too
        # stupid to do so themselves (ahem windows)

      //  stream_socket_sendto($this->socket, $data);
      //  limitedData = lazyByteSlice(data, 0, $this->SEND_LIMIT)

 /**       try:
            return untilConcludes($this->socket.send, limitedData)
        except socket.error as se:
            if se.args[0] in (EWOULDBLOCK, ENOBUFS):
                return 0
            else:
                return main.CONNECTION_LOST*/
    }

    public function _closeWriteConnection() {
  /*      try:
            getattr($this->socket, $this->_socketShutdownMethod)(1)
        except socket.error:
            pass
        p = interfaces.IHalfCloseableProtocol($this->protocol, None)
        if p:
            try:
                p.writeConnectionLost()
            except:
                f = failure.Failure()
                log.err()
                $this->connectionLost(f)*/
    }

    public function readConnectionLost($reason) {
     /*   p = interfaces.IHalfCloseableProtocol($this->protocol, None)
        if p:
            try:
                p.readConnectionLost()
            except:
                log.err()
                $this->connectionLost(failure.Failure())
        else:
            $this->connectionLost(reason)*/
    }


        /**See abstract.FileDescriptor.connectionLost().
        */
    public function connectionLost($reason=null) {
        # Make sure we're not called twice, which can happen e.g. if
        # abortConnection() is called from protocol's dataReceived and then
        # code immediately after throws an exception that reaches the
        # reactor. We can't rely on "disconnected" attribute for this check
        # since twisted.internet._oldtls does evil things to it:
        if (!$this->socket) {
             return;
        }
        parent::connectionLost($reason);
        $this->_closeSocket($reason/*not reason.check(error.ConnectionAborted)*/);
        $protocol = $this->protocol;
        unset($this->protocol);
        unset($this->socket);
        unset($this->fileno);
        $protocol->connectionLost($reason);

    }
    //public $logstr = "Uninitialized";

        /**Return the prefix to log with when I own the logging thread.
        */
    public function logPrefix() {
        return $this->logstr;
    }
    public function getTcpNoDelay() {
    //    return operator.truth($this->socket.getsockopt(socket.IPPROTO_TCP, socket.TCP_NODELAY));
    }
    public function setTcpNoDelay($enabled) {
    //    $this->socket.setsockopt(socket.IPPROTO_TCP, socket.TCP_NODELAY, enabled)
    }
    public function getTcpKeepAlive() {
      //  return operator.truth($this->socket.getsockopt(socket.SOL_SOCKET,
                                                  //   socket.SO_KEEPALIVE))
    }
    public function setTcpKeepAlive($enabled) {
      //  $this->socket.setsockopt(socket.SOL_SOCKET, socket.SO_KEEPALIVE, enabled)

    }
    
    public function abortConnection() {

    }
    
    abstract public function getPeer();
    
    abstract public function getHost();

}
