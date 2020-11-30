<?php

require_once __DIR__ . '/ServiceProvider.php';

class SimpleServiceProvider implements ServiceProvider
{
    private $services;

    public function __construct()
    {
        $this->services = [];
    }

    public function register($service)
    {
        $class = \get_class($service);
        foreach ($this->services as &$otherService)
        {
            if (\is_a($service, \get_class($otherService)) || \is_a($otherService, $class))
            {
                throw new \Exception("Clashing service types $class and " . get_class($otherService));
            }
        }
        $this->services[] = $service;
    }

    public function get($serviceClass)
    {
        foreach ($this->services as &$service)
        {
            if (\is_a($service, $serviceClass))
            {
                return $service;
            }
        }
        throw new \Exception("No service of type $serviceClass registered with this provider");
    }
}
