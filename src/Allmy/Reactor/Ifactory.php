<?php

interface IFactory {

    /**
     * Create an instance of a subclass of Protocol.
     *
     * The returned instance will handle input on an incoming server
     * connection, and an attribute \"factory\" pointing to the creating
     * factory.
     *
     * Override this method to alter how Protocol instances get created.
     *
     */
    public function buildProtocol($addr);
}
