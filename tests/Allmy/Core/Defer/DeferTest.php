<?php
namespace Allmy\Core\Defer;
use Allmy\Stream\Defer;

class DeferredTestCase extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->callbackResults = null;
        $this->errbackResults = null;
        $this->callback2Results = null;
        # Restore the debug flag to its original state when done.
        //$this->addCleanup(defer.setDebugging, defer.getDebugging())
    }
    public function _callback($args) {
        $this->callbackResults = $args;
        return $args[0];
    }
    public function _callback2($args) {
        $this->callback2Results = $args;
    }
    public function  _errback($args) {
        $this->errbackResults = $args;
    }

    public function testCallbackWithoutArgs() {
        $deferred  = new Deferred();
        $deferred->addCallback($this->_callback);
        $deferred->callback("hello");
        $this->assertEqual($this->errbackResults, null);
        $this->assertEqual($this->callbackResults, [] );
    }

}
