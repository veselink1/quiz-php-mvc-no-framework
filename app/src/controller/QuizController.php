<?php

require_once __DIR__ . '/../view/TemplateView.php';
require_once __DIR__ . '/../util/Response.php';

class QuizController
{
    /**
     * @var ServiceProvider
     */
    private $services;
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var QuizManager
     */
    private $quizManager;

    public function __construct($services)
    {
        $this->services = $services;
        $this->userManager = $services->get('UserManager');
        $this->quizManager = $services->get('QuizManager');
    }

    public function listAction()
    {
        $quizzes = $this->quizManager->getAvailableQuizzes(10);

        return new TemplateView($this->services, 'quiz_list', [
            'availableQuizzes' => $quizzes,
            'takenQuizzes' => [],
        ]);
    }

    public function quizAction($request)
    {
        if (!$this->userManager->isLoggedIn()) {
            Response::redirect('login');
        }

        $quiz = $this->quizManager->findById((int)$request['id']);
        return new TemplateView($this->services, 'quiz', [
            'quiz' => $quiz,
        ]);
    }
}
