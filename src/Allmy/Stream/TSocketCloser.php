<?php
namespace Allmy\Stream;

trait TSocketCloser {
    public $_socketShutdownMethod = 'shutdown';

    public function _closeSocket($orderly) {
        # The call to shutdown() before close() isn't really necessary, because
        # we set FD_CLOEXEC now, which will ensure this is the only process
        # holding the FD, thus ensuring close() really will shutdown the TCP
        # socket. However, do it anyways, just to be safe.
        $skt = $this->socket;
        /*try:
       //     if orderly:
                if ($this->_socketShutdownMethod) is not None:
                    getattr(skt, $this->_socketShutdownMethod)(2)
            else:
                # Set SO_LINGER to 1,0 which, by convention, causes a
                # connection reset to be sent when close is called,
                # instead of the standard FIN shutdown sequence.
                $this->socket.setsockopt(socket.SOL_SOCKET, socket.SO_LINGER,
                                       struct.pack("ii", 1, 0))

        except socket.error:
            pass
            try:*/
            stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);

      //  except socket.error:
     //   pass
    }
}
