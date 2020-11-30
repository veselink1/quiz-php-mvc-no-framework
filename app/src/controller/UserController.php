<?php

require_once __DIR__ . '/../view/TemplateView.php';
require_once __DIR__ . '/../util/Alert.php';
require_once __DIR__ . '/../util/Response.php';

class UserController
{
    private $services;
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct($services)
    {
        $this->services = $services;
        $this->userManager = $services->get('UserManager');
    }

    public function loginAction($request, $method)
    {
        $alerts = [];
        if ($method == 'POST') {
            try {
                $this->userManager->login($request['email'], $request['password']);
                Response::redirect('');
            } catch (\Throwable $th) {
                $alerts[] = new Alert('Please, check the credentials and try again!', Alert::ERROR);
            }
        }
        return new TemplateView($this->services, 'login', [
            'alerts' => $alerts,
        ]);
    }

    public function signupAction($request, $method)
    {
        if ($method == 'POST') {
            $this->userManager->register($request['email'], $request['password']);
            Response::setLocation('login');
            return new TemplateView($this->services, 'login', [
                'alerts' => [
                    new Alert('User registration succeeded!', Alert::SUCCESS)
                ],
            ]);
        }
        return new TemplateView($this->services, 'signup');
    }

    public function logoutAction($request, $method)
    {
        $this->userManager->logout();
        Response::redirect('');
    }
}
