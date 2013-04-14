<?php
    /**
    Core methods that a Reactor must implement.
     */

interface IReactorCore {

    running = Attribute(
        "A C{bool} which is C{True} from I{during startup} to "
        "I{during shutdown} and C{False} the rest of the time.")


    public function resolve(name, timeout=10);
        /**
        Return a L{twisted.internet.public functioner.Deferred} that will resolve a hostname.
	 */

    public function run();
        /**
        Fire 'startup' System Events, move the reactor to the 'running'
        state, then run the main loop until it is stopped with C{stop()} or
        C{crash()}.
	 */

    public function stop();
        /**
        Fire 'shutdown' System Events, which will move the reactor to the
        'stopped' state and cause C{reactor.run()} to exit.
	 */

    public function crash();
        /**
        Stop the main loop *immediately*, without firing any system events.

        This is named as it is because this is an extremely "rude" thing to do;
        it is possible to lose data and put your system in an inconsistent
        state by calling this.  However, it is necessary, as sometimes a system
        can become wedged in a pre-shutdown call.
	 */

    public function iterate(delay=0);
        /**
        Run the main loop's I/O polling function for a period of time.

        This is most useful in applications where the UI is being drawn "as
        fast as possible", such as games. All pending L{IDelayedCall}s will
        be called.

        The reactor must have been started (via the C{run()} method) prior to
        any invocations of this method.  It must also be stopped manually
        after the last call to this method (via the C{stop()} method).  This
        method is not re-entrant; you must not call it recursively; in
        particular, you must not call it while the reactor is running.
	 */

    public function fireSystemEvent(eventType);
        /**
        Fire a system-wide event.

        System-wide events are things like 'startup', 'shutdown', and
        'persist'.
	 */

    public function addSystemEventTrigger(phase, eventType, callable, *args, **kw);
        /**
        Add a function to be called when a system event occurs.

        Each "system event" in Twisted, such as 'startup', 'shutdown', and
        'persist', has 3 phases; 'before', 'during', and 'after' (in that
        order, of course).  These events will be fired internally by the
        Reactor.

        An implementor of this interface must only implement those events
        described here.

        Callbacks registered for the "before" phase may return either None or a
        Deferred.  The "during" phase will not execute until all of the
        Deferreds from the "before" phase have fired.

        Once the "during" phase is running, all of the remaining triggers must
        execute; their return values must be ignored.

        @param phase; a time to call the event -- either the string 'before',
                      'after', or 'during', describing when to call it
                      relative to the event's execution.

        @param eventType: this is a string describing the type of event.

        @param callable: the object to call before shutdown.

        @param args: the arguments to call it with.

        @param kw: the keyword arguments to call it with.

        @return: an ID that can be used to remove this call with
                 removeSystemEventTrigger.
	 */

    public function removeSystemEventTrigger(triggerID);
        /**
        Removes a trigger added with addSystemEventTrigger.

        @param triggerID: a value returned from addSystemEventTrigger.

        @raise KeyError: If there is no system event trigger for the given
            C{triggerID}.

        @raise ValueError: If there is no system event trigger for the given
            C{triggerID}.

        @raise TypeError: If there is no system event trigger for the given
            C{triggerID}.
	 */

    public function callWhenRunning(callable, *args, **kw);
        /**
        Call a function when the reactor is running.

        If the reactor has not started, the callable will be scheduled
        to run when it does start. Otherwise, the callable will be invoked
        immediately.

        @param callable: the callable object to call later.

        @param args: the arguments to call it with.

        @param kw: the keyword arguments to call it with.

        @return: None if the callable was invoked, otherwise a system
                 event id for the scheduled call.
	 */

}
