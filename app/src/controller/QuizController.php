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

        return $this->template('quiz_list', [
            'availableQuizzes' => $quizzes,
            'takenQuizzes' => [],
            'myQuizzes' => $myQuizzes,
        ]);
    }

    public function quizAction($request, $method)
    {
        $user = $this->requireLogin();

        $quiz = $this->quizManager->findById((int)$request['id']);
        $submission = $this->quizManager->getSubmission($user, $quiz);

        if ($method === 'POST') {
            $answers = [];
            foreach ($quiz->questions as &$question) {
                $answer = isset($request['q_' . $question->no]) ? $request['q_' . $question->no] : -1;
                $answers[$question->no] = $answer;
            }
            $this->quizManager->addSubmission($user, $quiz, $answers);
            $submission = $this->quizManager->getSubmission($user, $quiz);
        }

        return $this->template('quiz', [
            'quiz' => $quiz,
            'submission' => $submission,
        ]);
    }

    public function newQuizAction($request, $method)
    {
        $user = $this->requireStaff();

        if ($method === 'POST') {
            $available = (isset($request['available']) && $request['available'] === '1') ? 1 : 0;
            $id = $this->quizManager->addQuiz($user, $request['title'], (int)$request['duration'], $available);
            Response::redirect("quiz/edit?id=$id");
        } else {
            return $this->template('edit_quiz', ['quiz' => null]);
        }
    }

    public function deleteQuizAction($request, $method)
    {
        $user = $this->requireStaff();
        $quiz = $this->getAuthoredQuiz($user, $request['id']);

        if ($method === 'POST') {
            $this->quizManager->deleteQuiz($quiz);
        }
        Response::redirect('');
    }

    public function editQuizAction($request, $method)
    {
        $user = $this->requireStaff();
        $quiz = $this->getAuthoredQuiz($user, $request['id']);

        if ($method === 'POST') {
            $quiz->title = $request['title'];
            $quiz->duration = $request['duration'];
            $quiz->available = (isset($request['available']) && $request['available'] === '1') ? 1 : 0;
            $this->quizManager->updateQuiz($quiz);

            return $this->template('edit_quiz', [
                'quiz' => $quiz,
                'alerts' => [new Alert('Changes applies successfully!', Alert::SUCCESS)],
            ]);
        } else {
            return $this->template('edit_quiz', ['quiz' => $quiz]);
        }
    }

    public function addQuestionAction($request, $method)
    {
        $user = $this->requireStaff();
        $quiz = $this->getAuthoredQuiz($user, $request['quiz']);

        if ($method == 'POST') {
            $question_no = $request['no'];
            $this->quizManager->addQuestion(
                $quiz,
                $question_no,
                $request['text'],
                $this->getAnswerIndex($request['answer']),
                [
                    $request['opt_a'],
                    $request['opt_b'],
                    $request['opt_c'],
                    $request['opt_d'],
                ]
            );

            Response::setLocation("quiz/edit?id=$quiz->id");
            return $this->template('edit_quiz', [
                'quiz' => $quiz,
                'alerts' => [new Alert('Question added successfully!', Alert::SUCCESS)],
            ]);
        } else {
            return $this->template('edit_quiz_question', [
                'quiz' => $quiz,
                'question' => null,
            ]);
        }
    }


    public function editQuestionAction($request, $method)
    {
        $user = $this->requireStaff();
        $quiz = $this->getAuthoredQuiz($user, $request['quiz']);

        $query = [];
        parse_str($_SERVER['QUERY_STRING'], $query);
        $orig_no = $query['no'];

        $question = $this->quizManager->findQuestion($quiz, $orig_no);

        if ($method == 'POST') {
            $question->no = $request['no'];
            $question->text = $request['text'];
            $question->options = [
                $request['opt_a'],
                $request['opt_b'],
                $request['opt_c'],
                $request['opt_d'],
            ];
            $question->answer = $this->getAnswerIndex($request['answer']);

            $this->quizManager->updateQuestion(
                $quiz,
                $orig_no,
                $question
            );

            Response::setLocation("quiz/edit?id=$quiz->id");
            return $this->template('edit_quiz', [
                'quiz' => $quiz,
                'alerts' => [new Alert('Question updated successfully!', Alert::SUCCESS)],
            ]);
        } else {
            return $this->template('edit_quiz_question', [
                'quiz' => $quiz,
                'question' => $question,
            ]);
        }
    }

    public function deleteQuestionAction($request, $method)
    {
        $user = $this->requireStaff();
        $quiz = $this->getAuthoredQuiz($user, $request['quiz']);

        if ($method === 'POST') {
            $this->quizManager->deleteQuestion($quiz, $request['no']);
        }

        Response::redirect("quiz/edit?id=$quiz->id");
    }

    private function template($template, $context) {
        return new TemplateView($this->services, $template, $context);
    }

    private function requireStaff() {
        $user = $this->requireLogin();
        if (!$user->is_staff) {
            Response::redirect('login');
        }
        return $user;
    }

    private function requireLogin() {
        if (!$this->userManager->isLoggedIn()) {
            Response::redirect('login');
        }
        return $this->userManager->getCurrentUser();
    }

    private function getAuthoredQuiz($user, $id) {
        $quiz = $this->quizManager->findById($id);
        if ($quiz->author_id !== $user->id) {
            throw new \Exception('Unauthorized');
        }
        return $quiz;
    }

    private function getAnswerIndex($ans)
    {
        switch ($ans) {
            case 'a':
            case 'A':
                return 0;
            case 'b':
            case 'B':
                return 1;
            case 'c':
            case 'C':
                return 2;
            case 'd':
            case 'D':
                return 3;
        }
        throw new \Exception('Invalid value "' . $ans . '"!');
    }
}
