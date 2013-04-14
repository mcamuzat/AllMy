<?php
interface IReactorDaemonize {
    /**
    A reactor which provides hooks that need to be called before and after
    daemonization.

    Notes:
       - This interface SHOULD NOT be called by applications.
       - This interface should only be implemented by reactors as a workaround
         (in particular, it's implemented currently only by kqueue()).
         For details please see the comments on ticket #1918.
    */

    public function beforeDaemonize();
        /**
        Hook to be called immediately before daemonization. No reactor methods
        may be called until L{afterDaemonize} is called.

        @return: C{None}.
        */


    public function afterDaemonize();
        /**
        Hook to be called immediately after daemonization. This may only be
        called after L{beforeDaemonize} had been called previously.

        @return: C{None}.
        */
}
