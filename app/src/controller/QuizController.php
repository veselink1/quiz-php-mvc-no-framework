<?php

require_once __DIR__ . '/../view/TemplateView.php';

class QuizController
{
    public function __construct($userManager, $quizManager)
    {

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
            ]
        ];

        return new TemplateView('quiz_list', [
            'availableQuizzes' => $quizzes,
            'takenQuizzes' => [],
        ]);
    }
}
