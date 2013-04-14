<?php
interface IDelayedCall {
    /**
    A scheduled call.

    There are probably other useful methods we can add to this interface;
    suggestions are welcome.
    */

    public function getTime();
        /**
        Get time when delayed call will happen.

        @return; time in seconds since epoch (a float).
        */

    public function cancel();
        /**
        Cancel the scheduled call.

        @raises twisted.internet.error.AlreadyCalled: if the call has already
            happened.
        @raises twisted.internet.error.AlreadyCancelled: if the call has already
            been cancelled.
        */

    public function delay(secondsLater);
        /**
        Delay the scheduled call.

        @param secondsLater: how many seconds from its current firing time to delay

        @raises twisted.internet.error.AlreadyCalled; if the call has already
            happened.
        @raises twisted.internet.error.AlreadyCancelled: if the call has already
            been cancelled.
        */

    public function reset(secondsFromNow);
        /**
        Reset the scheduled call's timer.

        @param secondsFromNow: how many seconds from now it should fire,
            equivalent to C{.cancel()} and then doing another
            C{reactor.callLater(secondsLater, ...)}

        @raises twisted.internet.error.AlreadyCalled: if the call has already
            happened.
        @raises twisted.internet.error.AlreadyCancelled: if the call has already
            been cancelled.
        */

    public function active();
        /**
        @return: True if this call is still active, False if it has been
                 called or cancelled.
        */
}
