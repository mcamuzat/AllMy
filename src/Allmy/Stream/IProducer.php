<?php 

namespace Allmy\Stream;
/**
 * A producer produces data for a consumer.
 * 
 * Typically producing is done by calling the write method of an class
 * implementing L{IConsumer}.
 */
interface IProducer{

    /**
     * Stop producing data.
     * 
     * This tells a producer that its consumer has died, so it must stop
     * producing data for good.
     */

    public function stopProducing();
}
