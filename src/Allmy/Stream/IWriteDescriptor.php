<?php
namespace Allmy\Stream;
/**
 * An L{IFileDescriptor} that can write.
 *
 *This interface is generally used in conjunction with L{IReactorFDSet}.
 */
interface IWriteDescriptor extends IFileDescriptor {

    /**
     * Some data can be written to your descriptor.
     * 
     * @return: If an error is encountered which causes the descriptor to
     * no longer be valid, a L{Failure} should be returned.  Otherwise,
     * C{None}.
     */
    public function doWrite();
}
