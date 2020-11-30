<?php

class ViewUtilities
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct($services)
    {
        $this->userManager = $services->get('UserManager');
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

    public function isLoggedIn()
    {
        return $this->userManager->isLoggedIn();
    }

    public function getCurrentUser()
    {
        return $this->userManager->getCurrentUser();
    }
}
