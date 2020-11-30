<?php

class ViewUtilities
{
    private $currentUser;

    public function __construct($currentUser = null)
    {
        $this->currentUser = $currentUser;
    }

    public function static($path)
    {
        if ($path[0] != '/')
        {
            echo BASE_URL . '/' . $path;
        }
        echo BASE_URL . '/' . $path;
    }

    public function link($route)
    {
        echo ROUTER_BASE_URL . '/' . $route;
    }
}
