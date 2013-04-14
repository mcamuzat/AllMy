<?php

namespace Allmy\Socket;

use Evenement\EventEmitter;
use Allmy\Reactor\IReactor;

/** @event connection */
class Server extends EventEmitter implements ServerInterface
{
    public $master;
    private $reactor;

    public function __construct(IReactor $reactor)
    {
        $this->reactor = $reactor;
    }

    public function listen($port, $host = '127.0.0.1')
    {
        $this->master = @stream_socket_server("tcp://$host:$port", $errno, $errstr);
        if (false === $this->master) {
            $message = "Could not bind to tcp://$host:$port: $errstr";
            throw new Exception($message);
        }
        stream_set_blocking($this->master, 0);

        $that = $this;

        $this->reactor->addReadStream($this->master, function ($master) use ($that) {
            $newSocket = stream_socket_accept($master);
            if (false === $newSocket) {
                $that->emit('error', array(new \RuntimeException('Error accepting new connection')));

                return;
            }
            $that->handleConnection($newSocket);
        });
    }

    public function handleConnection($socket)
    {
        stream_set_blocking($socket, 0);

        $client = $this->createConnection($socket);

        $this->emit('connection', array($client));
    }

    public function getPort()
    {
        $name = stream_socket_get_name($this->master, false);

        return (int) substr(strrchr($name, ':'), 1);
    }

    public function shutdown()
    {
        $this->reactor->removeStream($this->master);
        fclose($this->master);
    }

    public function createConnection($socket)
    {
        return new Connection($socket, $this->reactor);
    }
}
