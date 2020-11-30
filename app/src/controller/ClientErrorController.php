<?php

require_once __DIR__ . '/../view/TemplateView.php';

class ClientErrorController
{
    private $services;

    public function __construct($services)
    {
        $this->services = $services;
    }

    public function notFoundAction()
    {
        return new TemplateView($this->services, 'not_found');
    }
}
