<?php
namespace Allmy\Tcp;
use Allmy\Stream\FileDescriptor;


/**
 * Basic implementation of a ListeningPort.
 *  Note: This does not actually implement IListeningPort.
 */
abstract class BasePort  extends FileDescriptor{

    protected $addressFamily = null;
    protected $socketType = null;

    protected function createInternetSocket() {
        $s = socket_create($this->addressFamily, $this->socketType,getprotobyname($this->_type));
        socket_set_nonblock($s);
        return $s;
    }
   
}
