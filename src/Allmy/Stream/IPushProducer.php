<?php

namespace Allmy\Stream;
/**
 * A push producer, also known as a streaming producer is expected to
 * produce (write to this consumer) data on a continuous basis, unless
 * it has been paused. A paused push producer will resume producing
 * after its resumeProducing() method is called.   For a push producer
 * which is not pauseable, these functions may be noops.
 */
interface IPushProducer extends IProducer {

    /**
     * Pause producing data.
     * 
     * Tells a producer that it has produced too much data to process for
     * the time being, and to stop until resumeProducing() is called.
     */
    public function pauseProducing();

    /**
     * Resume producing data.
     * 
     * This tells a producer to re-add itself to the main loop and produce
     * more data for its consumer.
     */
    public function resumeProducing();
}
