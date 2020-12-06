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
        $myQuizzes = [];

        if ($this->userManager->isLoggedIn() && ($user = $this->userManager->getCurrentUser())) {
            $myQuizzes = $this->quizManager->getQuizzesByAuthor($user, 10);
        }

        return new TemplateView($this->services, 'quiz_list', [
            'availableQuizzes' => $quizzes,
            'takenQuizzes' => [],
            'myQuizzes' => $myQuizzes,
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

    public function newQuizAction($request, $method)
    {
        if (!$this->userManager->isLoggedIn()) {
            Response::redirect('login');
        }

        $user = $this->userManager->getCurrentUser();
        if (!$user->is_staff) {
            Response::redirect('login');
        }

        if ($method === 'POST') {
            $id = $this->quizManager->addQuiz($user, $request['title'], (int)$request['duration'], 0);
            Response::redirect("quiz/edit?id=$id");
        } else {
            return new TemplateView($this->services, 'edit_quiz', ['quiz' => null]);
        }
    }

    public function editQuizAction($request, $method)
    {
        if (!$this->userManager->isLoggedIn()) {
            Response::redirect('login');
        }

        $user = $this->userManager->getCurrentUser();
        if (!$user->is_staff) {
            Response::redirect('login');
        }

        $quiz = $this->quizManager->findById($request['id']);
        if ($quiz->author_id !== $user->id) {
            throw new \Exception('Unauthorized');
        }

        if ($method === 'POST') {
            $quiz->title = $request['title'];
            $quiz->duration = $request['duration'];
            $this->quizManager->updateQuiz($quiz);
            Response::redirect("quiz/edit?id=$quiz->id");
        } else {
            return new TemplateView($this->services, 'edit_quiz', ['quiz' => $quiz]);
        }
    }
}
