<?php
namespace Allmy\Protocol;

use Allmy\Stream\IProducer;


    /**Generate repeating noise (RFC 864)*/
class Chargen extends Protocol implements IProducer{

    private $noise = '@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~ !"#$%&?';


    public function connectionMade() {
        $this->transport->registerProducer($this, 0);
    }
    public function resumeProducing() {
        $this->transport->write($this->noise);
    }
    public function pauseProducing() {
    }
    public function stopProducing() {
    }

}
