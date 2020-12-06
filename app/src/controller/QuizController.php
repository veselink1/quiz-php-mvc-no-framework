<?php

require_once __DIR__ . '/../view/TemplateView.php';
require_once __DIR__ . '/../util/Response.php';
require_once __DIR__ . '/../util/Alert.php';

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

    public function quizAction($request, $method)
    {
        if (!$this->userManager->isLoggedIn()) {
            Response::redirect('login');
        }

        $user = $this->userManager->getCurrentUser();
        $quiz = $this->quizManager->findById((int)$request['id']);
        $submission = $this->quizManager->getSubmission($user, $quiz);

        if ($method === 'POST') {
            $answers = [];
            foreach ($quiz->questions as &$question) {
                $answer = isset($request['q_' . $question->no]) ? $request['q_' . $question->no] : -1;
                $answers[$question->no] = $answer;
            }
            $this->quizManager->addSubmission($user, $quiz, $answers);
        }

        return new TemplateView($this->services, 'quiz', [
            'quiz' => $quiz,
            'submission' => $submission,
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
            $available = (isset($request['available']) && $request['available'] === '1') ? 1 : 0;
            $id = $this->quizManager->addQuiz($user, $request['title'], (int)$request['duration'], $available);
            Response::redirect("quiz/edit?id=$id");
        } else {
            return new TemplateView($this->services, 'edit_quiz', ['quiz' => null]);
        }
    }

    public function deleteQuizAction($request, $method)
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
            $this->quizManager->deleteQuiz($quiz);
        }
        Response::redirect('');
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
            $quiz->available = (isset($request['available']) && $request['available'] === '1') ? 1 : 0;
            $this->quizManager->updateQuiz($quiz);

            return new TemplateView($this->services, 'edit_quiz', [
                'quiz' => $quiz,
                'alerts' => [new Alert('Changes applies successfully!', Alert::SUCCESS)],
            ]);
        } else {
            return new TemplateView($this->services, 'edit_quiz', ['quiz' => $quiz]);
        }
    }
}
