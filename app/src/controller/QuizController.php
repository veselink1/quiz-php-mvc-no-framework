<?php

require_once __DIR__ . '/../view/TemplateView.php';
require_once __DIR__ . '/../util/Response.php';

class QuizController
{
    private $services;
    private $userManager;
    private $quizManager;

    public function __construct($services)
    {
        $this->services = $services;
        $this->userManager = $services->get('UserManager');
        $this->quizManager = $services->get('QuizManager');
    }

    public function listAction()
    {
        $quizzes = [
            (object)[
                'id' => 14,
                'name' => 'SQL Basics',
                'author' => 'Peter Parker',
                'available' => TRUE,
                'duration' => 60,
            ],
            (object)[
                'id' => 41,
                'name' => 'PHP Basics',
                'author' => 'Veselin Karaganev',
                'available' => TRUE,
                'duration' => 30,
            ]
        ];

        return new TemplateView($this->services, 'quiz_list', [
            'availableQuizzes' => $quizzes,
            'takenQuizzes' => [],
        ]);
    }

    public function quizAction()
    {
        if (!$this->userManager->isLoggedIn()) {
            Response::redirect('login');
        }

        $quiz = (object)[
            'id' => 41,
            'name' => 'PHP Basics',
            'author' => 'Veselin Karaganev',
            'available' => TRUE,
            'duration' => 30,
            'questions' => [
                1, 2, 3, 4 // Dummy values
            ],
        ];
        return new TemplateView($this->services, 'quiz', [
            'quiz' => $quiz,
        ]);
    }
}
