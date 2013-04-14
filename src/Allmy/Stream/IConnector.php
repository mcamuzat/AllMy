<?php
/**
 * Object used to interface between connections and protocols.
 *
 * Each L{IConnector} manages one connection.
 */
interface IConnector {
    /**
     * Stop attempting to connect.
     */
    public function stopConnecting();
    
    /** 
     * Disconnect regardless of the connection state.
     * If we are connected, disconnect, if we are trying to connect,
     * stop trying.
     */
    public function disconnect();
    
    /**
     * Try to connect to remote address.
     */
    public function connect();
    
    /**
     * Return destination this will try to connect to.
     *
     * @return An object which provides L{IAddress}.
     */
    public function getDestination();
}

