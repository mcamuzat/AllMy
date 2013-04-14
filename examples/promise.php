<?php
require __DIR__.'/../vendor/autoload.php';


$deferred = new Allmy\Promise\Deferred();

$promise  = $deferred->promise()->then(function ($a) {echo "tout va bien";})->then(function ($b) {echo "skkorien ne va";});


$deferred->resolve();
