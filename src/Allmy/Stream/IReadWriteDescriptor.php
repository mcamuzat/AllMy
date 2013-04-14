<?php
namespace Allmy\Stream;
/**
 * An L{IFileDescriptor} that can both read and write.
 */
interface IReadWriteDescriptor extends IReadDescriptor, IWriteDescriptor {
}
