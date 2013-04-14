<?php 
namespace Allmy\Stream;
/**
 * An object that wraps a networking OS-specific handle.
 */
interface ISystemHandle {
    /**
     * Return a system- and reactor-specific handle.
     * 
     * This might be a socket.socket() object, or some other type of
     * object, depending on which reactor is being used. Use and
     * manipulate at your own risk.
     * 
     * This might be used in cases where you want to set specific
     * options not exposed by the Twisted APIs.
     */
    public function getHandle();
}
