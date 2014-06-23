<?php

namespace Allmy\Protocol;

/**
 * A protocol that receives lines and/or raw data, depending on mode.
 *
 * In line mode, each line that's received becomes a callback to
 * L{lineReceived}.  In raw data mode, each chunk of raw data becomes a
 * callback to L{rawDataReceived}.  The L{setLineMode} and L{setRawMode}
 * methods switch between the two modes.

 * This is useful for line-oriented protocols such as IRC, HTTP, POP, etc.

 * @cvar delimiter: The line-ending delimiter to use. By default this is
 *                  C{b'\\r\\n'}.
 * @cvar MAX_LENGTH: The maximum length of a line to allow (If a
 * sent line is longer than this, the connection is dropped).
 * Default is 16384.
 */
abstract class LineReceiver extends Protocol {
    private $line_mode = 1;
    private $_buffer = '';
    private $_busyReceiving = False;
    private $delimiter = "\r\n";
    protected $MAX_LENGTH = 16384;
    private $paused = false;



    /**
     * Clear buffered data.
     * 
     * @return: All of the cleared buffered data.
     * @rtype: C{bytes}
     */
    public function clearLineBuffer() {
        $b = $this->_buffer;
        $this->_buffer =  '';
        return $b;
    }
    /**
     * Protocol.dataReceived.
     * Translates bytes into lines, and calls lineReceived (or
     * rawDataReceived, depending on mode.)
     */

    public function dataReceived($data) {
        if ($this->_busyReceiving) {
            $this->_buffer .= $data;
            return;
        }

        try {
            $this->_busyReceiving = True;
            $this->_buffer .= $data;
            while (strlen($this->_buffer) and !$this->paused) {
                if ($this->line_mode) {
                    $line = $this->_buffer;
                    if (strlen($this->_buffer) > $this->MAX_LENGTH) {
                        $line = $this->_buffer;
                        $this->_buffer = '';
                        $result =  $this->lineLengthExceeded($line);
                        break;
                    } else {
                        $lineLength = strlen($line);
                            if ($lineLength > $this->MAX_LENGTH) {
                                $exceeded = $line . $this->_buffer;
                                $this->_buffer = '';
                                $result = $this->lineLengthExceeded($exceeded);
                                break;
                            }
                        $result = $this->lineReceived($line);
                        $this->_buffer = '';
                        if ($result or $this->transport/* and $this->transport->disconnecting*/) {
                            break;
                        }
                    }
                } else {
                    $data = $this->_buffer;
                    $this->_buffer = '';
                    $why = $this->rawDataReceived($data);
                    if ($why) {
                        $result = $why;
                        break;
                    }
                }
            }
        } catch (Exception $exception) {

        }
        $this->_busyReceiving = False;
    }


    /**
     * Sets the line-mode of this receiver.
     * 
     * If you are calling this from a rawDataReceived callback,
     * you can pass in extra unhandled data, and that data will
     * be parsed for lines.  Further data received will be sent
     * to lineReceived rather than rawDataReceived.
     * 
     * Do not pass extra data if calling this function from
     * within a lineReceived callback.
     */
    public function setLineMode($extra='') {
        $this->line_mode = 1;
        if ($extra) {
            return $this->dataReceived($extra);
        }
    }

    /**
     * Sets the raw mode of this receiver.
     * Further data received will be sent to rawDataReceived rather
     * than lineReceived.
     */
    public function setRawMode() {
        $this->line_mode = 0;
    }

    /**
     * Override this for when raw data is received.
     */
    public function rawDataReceived($data) {

    }

    /**
     * Override this for when each line is received.
     * 
     * @param line: The line which was received with the delimiter removed.
     * @type line: C{bytes}
     */
    abstract public function lineReceived($line);

    /**
     * Sends a line to the other end of the connection.
     * 
     * @param line: The line to send, not including the delimiter.
     * @type line: C{bytes}
     */
    public function sendLine($line) 
    {
        return $this->transport->write($line.$this->delimiter);
    }

    /**
     * Called when the maximum line length has been reached.
     * Override if it needs to be dealt with in some special way.
     * 
     * The argument 'line' contains the remainder of the buffer, starting
     * with (at least some part) of the line which is too long. This may
     * be more than one line, or may be only the initial portion of the
     * line.
     */
    public function lineLengthExceeded($line) {
        return $this->transport->loseConnection();
    }
}
