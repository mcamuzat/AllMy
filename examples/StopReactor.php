<?php
require __DIR__.'/../vendor/autoload.php';

class Countdown {
    public $count= 5;
    public $reactor;

    public function __construct($reactor)
    {
        $this->reactor = $reactor;
    }

    public function counting()
    {
            if (($this->count) == 0) {
            $this->reactor->stop();
        } else {
            echo "...\n" ;
            $this->count--;
            $this->reactor->addTimer(1, array($this, "counting"));
        }
    }
}

$loop = Allmy\Reactor\Factory::create();
$count = new Countdown($loop);
echo "start\n";
$loop->callWhenRunning(array($count, "counting"));
$loop->run();
echo "stop\n";
