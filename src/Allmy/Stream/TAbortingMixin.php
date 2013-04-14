<?php 
/**
 * Common implementation of C{abortConnection}.
 * 
 * @ivar _aborting: Set to C{True} when C{abortConnection} is called.
 * @type _aborting: C{bool}
 */

namespace Allmy\Stream;

trait TAbortingMixin {
    public $_aborting = False;
    
    public function abortConnection() {
        /**
         * Aborts the connection immediately, dropping any buffered data.
         * 
         * @since: 11.1
         */
        if ($this->disconnected or $this->_aborting) {
            return;
        }
        $this->_aborting = True;
        $this->stopReading();
        $this->stopWriting();
      //  $this->doRead();
     //   $this->doWrite = lambda *args, **kwargs: None
     //   $this->reactor.callLater(0, $this->connectionLost, failure.Failure(error.ConnectionAborted()))
    }
}
