<?php
    /*
    UDP socket methods.
     */
interface IReactorUDP {
        /*
        Connects a given DatagramProtocol to the given numeric UDP port.

        @return: object which provides L{IListeningPort}.
         */

    public function listenUDP(port, protocol, interface='', maxPacketSize=8192);
}
