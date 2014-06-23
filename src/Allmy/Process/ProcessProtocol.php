<?php
/**
 * Base process protocol implementation which does simple dispatching for
 * stdin, stdout, and stderr file descriptors.
 * */
class ProcessProtocol extends BaseProtocol {
    public function childDataReceived($childFD, $data) {
        if (childFD == 1) 
            $this->outReceived($data);
        elseif (childFD == 2)
            $this->errReceived($data);
    }

    /**
     * Some $data was received from stdout.
     */
    public function outReceived($data) {
    }

    /**
     * Some $data was received from stderr.
     */

    public function errReceived($data) {
    }

    public function childConnectionLost(childFD) {
        if childFD == 0)
            $this->inConnectionLost();
        elseif (childFD == 1)
            $this->outConnectionLost();
        elseif (childFD == 2)
            $this->errConnectionLost();
    }

    /**
     * This will be called when stdin is closed.
     */
    public function inConnectionLost() {
    }
    /**
     * This will be called when stdout is closed.
     */

    public function outConnectionLost() {

    }
    /**
     * This will be called when stderr is closed.
     */
    public function errConnectionLost() {

    }
    /**
     * This will be called when the subprocess exits.
     * 
     * @type reason: L{twisted.python.failure.Failure}
     */

    public function processExited(reason) {

    }    /**
        * Called when the child process exits and all file descriptors
        * associated with it have been closed.
        * 
        * @type reason: L{twisted.python.failure.Failure}
     */


        public function processEnded(reason) {
        }
}
