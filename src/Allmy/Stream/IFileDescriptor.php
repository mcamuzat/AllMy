<?php 
namespace Allmy\Stream;
/**
 * An interface representing a UNIX-style numeric file descriptor.
 */
interface IFileDescriptor {
    /**
     * @raise: If the descriptor no longer has a valid file descriptor
     * number associated with it.
     * 
     * @return: The platform-specified representation of a file descriptor
     * number.  Or C{-1} if the descriptor no longer has a valid file
     * descriptor number associated with it.  As long as the descriptor
     * is valid, calls to this method on a particular instance must
     * return the same value.
     */
    public function fileno();


    /**
     * Called when the connection was lost.
     * 
     * This is called when the connection on a selectable object has been
     * lost.  It will be called whether the connection was closed explicitly,
     * an exception occurred in an event handler, or the other end of the
     * connection closed it first.
     * * 
     * See also L{IHalfCloseableDescriptor} if your descriptor wants to be
     * notified separately of the two halves of the connection being closed.
     * 
     * @param reason: A failure instance indicating the reason why the
     * connection was lost.  L{error.ConnectionLost} and
     * L{error.ConnectionDone} are of special note, but the
     * failure may be of other classes as well.
     */
    public function connectionLost($reason);
}
