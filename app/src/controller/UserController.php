<?php

require_once __DIR__ . '/../view/TemplateView.php';

class UserController
{
    private $userManager;

    public function __construct($services)
    {
        $this->userManager = $services->get('UserManager');
    }

    public function loginAction($request, $method)
    {
        if ($method == 'POST')
        {
            $this->userManager->login($request['email'], $request['password']);
        }
        return new TemplateView('login');
    }
}
