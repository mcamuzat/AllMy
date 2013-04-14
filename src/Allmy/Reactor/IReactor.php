<?php

namespace Allmy\Reactor;
use Allmy\Protocol\IProtocolFactory;

interface IReactor
{
    public function addReadStream($stream, $listener);
    public function addWriteStream($stream, $listener);
    public function addReader($stream);
    public function addWriter($stream);


    public function removeReadStream($stream);
    public function removeWriteStream($stream);
    public function removeStream($stream);

    public function addTimer($interval, $callback);
    public function addPeriodicTimer($interval, $callback);
    public function cancelTimer($signature);

    public function tick();
    public function run();
    public function stop();
    public function listenTCP($port, IProtocolFactory $factory, $backlog=50, $interface='');

}

