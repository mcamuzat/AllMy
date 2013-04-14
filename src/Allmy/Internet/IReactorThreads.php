<?php
interface IReactorThreads {
    /**
    Dispatch methods to be run in threads.

    Internally, this should use a thread pool and dispatch methods to them.
    */

    public function getThreadPool();
        /**
        Return the threadpool used by L{callInThread}.  Create it first if
        necessary.

        @rtype: L{twisted.python.threadpool.ThreadPool}
        */


    public function callInThread(callable, *args, **kwargs);
        /**
        Run the callable object in a separate thread.
        */


    public function callFromThread(callable, *args, **kw);
        /**
        Cause a function to be executed by the reactor thread.

        Use this method when you want to run a function in the reactor's thread
        from another thread.  Calling L{callFromThread} should wake up the main
        thread (where L{reactor.run()<reactor.run>} is executing) and run the
        given callable in that thread.

        If you're writing a multi-threaded application the C{callable} may need
        to be thread safe, but this method doesn't require it as such. If you
        want to call a function in the next mainloop iteration, but you're in
        the same thread, use L{callLater} with a delay of 0.
        */


    public function suggestThreadPoolSize(size);
        /**
        Suggest the size of the internal threadpool used to dispatch functions
        passed to L{callInThread}.
        */

}
