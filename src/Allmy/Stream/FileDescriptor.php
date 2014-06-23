<?php 
/**
 * An object which can be operated on by select().
 *
 * This is an abstract superclass of all objects which may be notified when
 * they are readable or writable; e.g. they have a file-descriptor that is
 * valid to be passed to select(2).
 */
namespace Allmy\Stream;

abstract class FileDescriptor implements IPushProducer, IReadWriteDescriptor, IConsumer {
    use TConsumerMixin;
    protected $connected = 0;
    protected $disconnected = 0;
    protected $disconnecting = 0;
    protected $_writeDisconnecting = False;
    protected $_writeDisconnected = False;
    protected $dataBuffer = "";
    protected $offset = 0;
    public $producer = Null;
    public $bufferSize = 65536; //2**2**2**2

    const SEND_LIMIT = 131072;

    function __construct($reactor)
    {
        $this->reactor = $reactor;
        $this->_tempDataBuffer = array(); # will be added to dataBuffer in doWrite
        $this->_tempDataLen = 0;

    } 



    public function _concatenate($bObj, $offset, array $bArray)
    {
        # Python 3 lacks the buffer() builtin and the other primitives don't
        # help in this case.  Just do the copy.  Perhaps later these buffers can
        # be joined and FileDescriptor can use writev().  Or perhaps bytearrays
        # would help.
        return substr($bObj, $offset) . implode("" ,$bArray);

    }


    /**
     * The connection was lost.
     * This is called when the connection on a selectable object has been
     * lost.  It will be called whether the connection was closed explicitly,
     * an exception occurred in an event handler, or the other end of the
     * connection closed it first.
     *
     * Clean up state here, but make sure to call back up to FileDescriptor.
     */
    public function connectionLost($reason)
    {
        $this->disconnected = 1;
        $this->connected = 0;
        if ($this->producer) {
            $this->producer->stopProducing();
            $this->producer = null;
        }
        $this->stopReading();
        $this->stopWriting();

    }


    /**
     * Write as much as possible of the given data, immediately.
     *
     * This is called to invoke the lower-level writing functionality, such
     * as a socket's send() method, or a file's write(); this method
     *returns an integer or an exception.  If an integer, it is the number
     * of bytes written (possibly zero); if an exception, it indicates the
     *connection was lost.
     */

    public function writeSomeData($data) {
        //  throw NotImplementedError("%s does not implement writeSomeData" %
        //                          reflect.qual($this->__class__))
    }

    /**
     * Called when data is available for reading.
     *
     * Subclasses must override this method. The result will be interpreted
     * in the same way as a result of doWrite().
     */
    public abstract function doRead() ;


    /**
     * Called when data can be written.
     * 
     * @return: C{None} on success, an exception or a negative integer on
     * failure.
     * 
     * @see: L{twisted.internet.interfaces.IWriteDescriptor.doWrite}.
     */
    public function doWrite() {

        if (strlen($this->dataBuffer) - $this->offset < self::SEND_LIMIT) {
            # If there is currently less than SEND_LIMIT bytes left to send
            # in the string, extend it with the array data.
            $this->dataBuffer = $this->_concatenate(
                $this->dataBuffer, $this->offset, $this->_tempDataBuffer);
            $this->offset = 0;
            $this->_tempDataBuffer = [];
            $this->_tempDataLen = 0;
        }

        # Send as much data as you can.
        if ($this->offset) {
            $l = $this->writeSomeData(lazyByteSlice($this->dataBuffer, $this->offset));
        } else {
            $l = $this->writeSomeData($this->dataBuffer);
        }
        # There is no writeSomeData implementation in Twisted which returns
        # < 0, but the documentation for writeSomeData used to claim negative
        # integers meant connection lost.  Keep supporting this here,
        # although it may be worth deprecating and removing at some point.
        //if isinstance(l, Exception) or l < 0:
        //    return l
        $this->offset += $l;

        # If there is nothing left to send,

        if ($this->offset == strlen($this->dataBuffer) && !$this->_tempDataLen) {
            $this->dataBuffer = "";
            $this->offset = 0;
            # stop writing.
            $this->stopWriting();
            # If I've got a producer who is supposed to supply me with data,
            if ($this->producer && ((!$this->streamingProducer) || $this->producerPaused)) {
                # tell them to supply some more.
                $this->producerPaused = 0;
                $this->producer->resumeProducing();
            }
            elseif ($this->disconnecting) {
                # But if I was previously asked to let the connection die, do
                # so.
                return $this->_postLoseConnection();
            }
            elseif ($this->_writeDisconnecting) {
                # I was previously asked to half-close the connection.  We
                # set _writeDisconnected before calling handler, in case the
                # handler calls loseConnection(), which will want to check for
                # this attribute.
                $this->_writeDisconnected = True;
                $result = $this->_closeWriteConnection();
                return $result;
            }
        }
        return null;

    }

    public function _postLoseConnection() {
        /**
         * Called after a loseConnection(), when all data has been written.
         *
         * Whatever this returns is then returned by doWrite.
         */
        # default implementation, telling reactor we're finished
        //return main.CONNECTION_DONE
    }


    public function _closeWriteConnection() {
        # override in subclasses
        // pass
    }


    public function writeConnectionLost($reason) {
        # in current code should never be called
        $this->connectionLost($reason);
    }


    public function readConnectionLost($reason) {
        # override in subclasses
        $this->connectionLost($reason);
    }


    /**
     * Determine whether the user-space send buffer for this transport is full
     * or not.
     * 
     * When the buffer contains more than C{self.bufferSize} bytes, it is
     * considered full.  This might be improved by considering the size of the
     * kernel send buffer and how much of it is free.
     * 
     * @return: C{True} if it is full, C{False} otherwise.
     */
    public function _isSendBufferFull() {
        return ((strlen($this->dataBuffer) + $this->_tempDataLen) > $this->bufferSize);

    }


    /**
     * Possibly pause a producer, if there is one and the send buffer is full.
     */
    public function _maybePauseProducer() {
        # If we are responsible for pausing our producer,
        if (!$this->producer && $this->streamingProducer) {
            # and our buffer is full,
            if ($this->_isSendBufferFull()) {
                # pause it.
                $this->producerPaused = 1;
                $this->producer->pauseProducing();
            }
        }

    }


    /**
     * Reliably write some data.
     * 
     * The data is buffered until the underlying file descriptor is ready
     * for writing. If there is more than C{self.bufferSize} data in the
     * buffer and this descriptor has a registered streaming producer, its
     * C{pauseProducing()} method will be called.
     */
    public function write($data) {
        // if isinstance(data, unicode): # no, really, I mean it
        //     raise TypeError("Data must not be unicode")
        if (!$this->connected || $this->_writeDisconnected){ 
            return;
        }
        if ($data) {
            $this->_tempDataBuffer[] = $data;
            $this->_tempDataLen += strlen($data);
            $this->_maybePauseProducer();
            $this->startWriting();
        }
    }

    /**
     * Reliably write a sequence of data.
     *
     * Currently, this is a convenience method roughly equivalent to::
     *
     * for chunk in iovec:
     *    fd.write(chunk)
     *
     * It may have a more efficient implementation at a later time or in a
     * different reactor.
     * As with the C{write()} method, if a buffer size limit is reached and a
     * streaming producer is registered, it will be paused until the buffered
     * data is written to the underlying file descriptor.
     */

    public function writeSequence($iovec) {
     /*   for i in iovec:
            if isinstance(i, unicode): # no, really, I mean it
                raise TypeError("Data must not be unicode")
                if not $this->connected or not iovec or $this->_writeDisconnected:
                    return
                    $this->_tempDataBuffer.extend(iovec)
                    for i in iovec:
                        $this->_tempDataLen += len(i)
                        $this->_maybePauseProducer()
     $this->startWriting()*/
    }


    /**
     * Close the connection at the next available opportunity.
     *
     * Call this to cause this FileDescriptor to lose its connection.  It will
     * first write any data that it has buffered.
     * 
     * If there is data buffered yet to be written, this method will cause the
     * transport to lose its connection as soon as it's done flushing its
     * write buffer.  If you have a producer registered, the connection won't
     * be closed until the producer is finished. Therefore, make sure you
     * unregister your producer when it's finished, or the connection will
     * never close.
     */
    public function loseConnection( /* _connDone=failure.Failure(main.CONNECTION_DONE)*/) {
        if ($this->connected && !$this->disconnecting) {
            if ($this->_writeDisconnected) {
                # doWrite won't trigger the connection close anymore
                $this->stopReading();
                $this->stopWriting();
                $this->connectionLost(/*_connDone*/);
            }
            else {
                $this->stopReading();
                $this->startWriting();
                $this->disconnecting = 1;
            }
        }
    }


    public function loseWriteConnection() {
        $this->_writeDisconnecting = True;
        $this->startWriting();
    }

    /**
     * Stop waiting for read availability.
     *
     * 
     * Call this to remove this selectable from being notified when it is
     * ready for reading.
     *
     * @return void
     */
    public function stopReading() {
        $this->reactor->removeReader($this);

    }

    /**
     * Stop waiting for write availability.
     * Call this to remove this selectable from being notified when it is ready
     * for writing.
     *
     * @return void
     */
    public function stopWriting() {
        $this->reactor->removeWriter($this);

    }

    /**
     * Start waiting for read availability.
     *
     * @return void
     */
    public function startReading() {
        $this->reactor->addReader($this);
    }

    /**
     * Start waiting for write availability.
     *
     * Call this to have this FileDescriptor be notified whenever it is ready for
     * writing.
     */
    public function startWriting() {
        $this->reactor->addWriter($this);
    }
    # Producer/consumer implementation

    # first, the consumer stuff.  This requires no additional work, as
    # any object you can write to can be a consumer, really.

    /**
     * Stop consuming data.
     *
     * This is called when a producer has lost its connection, to tell the
     * consumer to go lose its connection (and break potential circular
     * references).
     */

    public function stopConsuming() {
        $this->unregisterProducer();
        $this->loseConnection();

    }

    /**
     * producer interface implementation
     */
    public function resumeProducing()
    {
        //assert $this->connected and not $this->disconnecting
        $this->startReading();
    }

    public function pauseProducing()
    {
        $this->stopReading();
    }

    public function stopProducing()
    {
        $this->loseConnection();
    }

    /**
     * File Descriptor number for select().
     *
     * This method must be overridden or assigned in subclasses to
     * indicate a valid file descriptor for the operating system.
     */
    public function fileno() {
        return -1;
    }
}
