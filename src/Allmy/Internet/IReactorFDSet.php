<?php
interface IReactorFDSet {
    /**
    Implement me to be able to use L{IFileDescriptor} type resources.

    This assumes that your main-loop uses UNIX-style numeric file descriptors
    (or at least similarly opaque IDs returned from a .fileno() method)
    */

    public function addReader(reader);
        /**
        I add reader to the set of file descriptors to get read events for.

        @param reader: An L{IReadDescriptor} provider that will be checked for
                       read events until it is removed from the reactor with
                       L{removeReader}.

        @return: C{None}.
        */

    public function addWriter(writer);
        /**
        I add writer to the set of file descriptors to get write events for.

        @param writer: An L{IWriteDescriptor} provider that will be checked for
                       write events until it is removed from the reactor with
                       L{removeWriter}.

        @return: C{None}.
        */

    public function removeReader(reader);
        /**
        Removes an object previously added with L{addReader}.

        @return: C{None}.
        */

    public function removeWriter(writer);
        /**
        Removes an object previously added with L{addWriter}.

        @return: C{None}.
        */

    public function removeAll();
        /**
        Remove all readers and writers.

        Should not remove reactor internal reactor connections (like a waker).

        @return: A list of L{IReadDescriptor} and L{IWriteDescriptor} providers
                 which were removed.
        */

    public function getReaders();
        /**
        Return the list of file descriptors currently monitored for input
        events by the reactor.

        @return: the list of file descriptors monitored for input events.
        @rtype: C{list} of C{IReadDescriptor}
        */

    public function getWriters();
        /**
        Return the list file descriptors currently monitored for output events
        by the reactor.

        @return: the list of file descriptors monitored for output events.
        @rtype: C{list} of C{IWriteDescriptor}
        */
}
