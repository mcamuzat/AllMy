<?php
    /**
    Time methods that a Reactor should implement.
    */
interface IReactorTime {

        /**
        Get the current time in seconds.

        @return: A number-like object of some sort.
        */
    public function seconds();


        /**
        Call a function later.

        @type delay:  C{float}
        @param delay: the number of seconds to wait.

        @param callable: the callable object to call later.

        @param args: the arguments to call it with.

        @param kw: the keyword arguments to call it with.

        @return: An object which provides L{IDelayedCall} and can be used to
                 cancel the scheduled call, by calling its C{cancel()} method.
                 It also may be rescheduled by calling its C{delay()} or
                 C{reset()} methods.
        */
    public function callLater(delay, callable, *args, **kw);


        /**
        Retrieve all currently scheduled delayed calls.

        @return: A tuple of all L{IDelayedCall} providers representing all
                 currently scheduled calls. This is everything that has been
                 returned by C{callLater} but not yet called or canceled.
        */
    public function getDelayedCalls();
}
