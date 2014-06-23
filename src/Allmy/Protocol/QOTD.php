<?php

namespace Allmy\Protocol;

/**Return a quote of the day (RFC 865)*/
class QOTD extends Protocol {

    public function connectionMade() {
        $this->transport->write($this->getQuote());
        $this->transport->loseConnection();
    }
    /** Return a quote. May be overrriden in subclasses.*/
    public function getQuote() {
        return "An apple a day keeps the doctor away.\r\n";
    }
}

