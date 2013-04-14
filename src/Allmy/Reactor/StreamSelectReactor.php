<?php

namespace Allmy\Reactor;

use Allmy\Reactor\Timer\Timers;
use Allmy\Protocol\IProtocolFactory;
use Allmy\Tcp\Port;

class StreamSelectReactor implements IReactor
{
    const QUANTUM_INTERVAL = 1000000;

    private $timers;
    private $running = false;
    private $readStreams = array();
    private $readers = array();
    private $writers = array();
    private $readListeners = array();
    private $writeStreams = array();
    private $writeListeners = array();

    public function __construct()
    {
        $this->timers = new Timers($this);
    }

    public function addReadStream($stream, $listener)
    {
        $id = (int) $stream;

        if (!isset($this->readStreams[$id])) {
            $this->readStreams[$id] = $stream;
            $this->readListeners[$id] = $listener;
        }
    }

    public function addReader($filedescriptor)
    {
        
        $id = (int) $filedescriptor->socket;

        if (!isset($this->readStreams[$id])) {
            $this->readStreams[$id] = $filedescriptor->socket;
            $this->readers[$id] = $filedescriptor;
        }
    }

    public function addWriter($filedescriptor)
    {
        $id = (int) $filedescriptor->socket;
        echo $id;
        if (!isset($this->writeStreams[$id])) {
            $this->writeStreams[$id] = $filedescriptor->socket;
            $this->writers[$id] = $filedescriptor;
        }
    }

    public function removeReader($filedescriptor)
    {
        
        $id = (int) $filedescriptor->socket;

        if (isset($this->readStreams[$id])) {
            unset($this->readStreams[$id]);
            unset($this->readers[$id]);
        }
    }
    public function removeWriter($filedescriptor)
    {
        $id = (int) $filedescriptor->socket;
        if (isset($this->writeStreams[$id])) {
            unset($this->writeStreams[$id]);
            unset($this->writers[$id]);
        }
    }

    public function addWriteStream($stream, $listener)
    {
        $id = (int) $stream;
        if (!isset($this->writeStreams[$id])) {
            $this->writeStreams[$id] = $stream;
            $this->writeListeners[$id] = $listener;
        }
    }

    public function removeReadStream($stream)
    {
        $id = (int) $stream;

        unset(
            $this->readStreams[$id],
            $this->readListeners[$id]
        );
    }

    public function removeWriteStream($stream)
    {
        $id = (int) $stream;

        unset(
            $this->writeStreams[$id],
            $this->writeListeners[$id]
        );
    }

    public function removeStream($stream)
    {
        $this->removeReadStream($stream);
        $this->removeWriteStream($stream);
    }

    public function addTimer($interval, $callback)
    {
        return $this->timers->add($interval, $callback);
    }

    public function addPeriodicTimer($interval, $callback)
    {
        return $this->timers->add($interval, $callback, true);
    }

    public function cancelTimer($signature)
    {
        $this->timers->cancel($signature);
    }

    protected function getNextEventTimeInMicroSeconds()
    {
        $nextEvent = $this->timers->getFirst();
        
        if (null === $nextEvent) {
            return self::QUANTUM_INTERVAL;
        }

        $currentTime = microtime(true);
        if ($nextEvent > $currentTime) {
            return ($nextEvent - $currentTime) * 1000000;
        }

        return 0;
    }

    protected function sleepOnPendingTimers()
    {
        if ($this->timers->isEmpty()) {
            $this->running = false;
        } else {
            // We use usleep() instead of stream_select() to emulate timeouts
            // since the latter fails when there are no streams registered for
            // read / write events. Blame PHP for us needing this hack.
            usleep($this->getNextEventTimeInMicroSeconds());
        }
    }

    protected function runStreamSelect()
    {
        $read = $this->readStreams ?: null;
        $write = $this->writeStreams ?: null;
        $except = null;
          if (!$read && !$write) {
            $this->sleepOnPendingTimers();

            return;
        }

        
        if (stream_select($read, $write, $except, 0, $this->getNextEventTimeInMicroSeconds()) > 0) {
            if ($read) {
                foreach ($read as $stream) {
                    $id = (int) $stream;
                    if (isset($this->readers[$id])) {
                        $this->readers[$id]->doRead();
                    }
                 }
            }
            if ($write) {
                foreach ($write as $stream) {
                    $id = (int) $stream;
                    if (isset($this->writers[$id])) {
                        $this->writers[$id]->doWrite();
                    }
                 }
            }

        }
    }
  

    public function tick()
    {
        $this->timers->tick();
        $this->runStreamSelect();

        return $this->running;
    }

    public function run()
    {
        $this->running = true;

        while ($this->tick()) {
            // NOOP
        }
    }

    public function stop()
    {
        $this->running = false;
    }


    public function listenTCP($port, IProtocolFactory $factory, $backlog=50, $interface='127.0.0.1')
    {
        $port = new Port($port, $factory,$backlog,$interface,$this);
        $port->startListening();

    }

}
