<?php

namespace Allmy\Stream;
/**
 * An L{IFileDescriptor} that can read.
 * 
 *  This interface is generally used in conjunction with L{IReactorFDSet}.
 */
interface IReadDescriptor extends IFileDescriptor {
    /**
     *  Some data is available for reading on your descriptor.
     *
     *  @return: If an error is encountered which causes the descriptor to
     *     no longer be valid, a L{Failure} should be returned.  Otherwise,
     *     C{None}.
     */
    public function doRead();
}
