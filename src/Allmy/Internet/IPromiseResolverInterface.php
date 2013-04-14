<?php 
interface ResolverInterface
{
    public function resolve($result = null);
    public function reject($reason = null);
    public function progress($update = null);
}
