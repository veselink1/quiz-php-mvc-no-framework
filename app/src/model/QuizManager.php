<?php

interface QuizManager
{
    public function __construct($services);
    public function getAvailableQuizzes($limit, $offset = 0);
    public function findById($id);
}

class PersistentQuizManager implements QuizManager
{
    /**
     * @var DbContext
     */
    private $db;

    public function __construct($services)
    {
        $this->db = $services->get('DbContext');
    }

    public function getAvailableQuizzes($limit, $offset = 0)
    {
        throw new \Exception('not implemented');
    }

    public function findById($id)
    {
        throw new \Exception('not implemented');
    }
}

$dummy_quiz_set = [
    (object)[
        'id' => 14,
        'name' => 'SQL Basics',
        'author' => 'Peter Parker',
        'available' => TRUE,
        'duration' => 60,
        'questions' => [
            (object)[
                'text' => 'Which SQL statement is used to extract data from a database?',
                'options' => ['SELECT', 'OPEN', 'EXTRACT', 'GET'],
                'answer' => 0,
            ],
            (object)[
                'text' => 'Which SQL statement is used to insert new data into a database?',
                'options' => ['INSERT NEW', 'INSERT INTO', 'ADD RECORD', 'ADD NEW'],
                'answer' => 1,
            ],
        ],
        'responses' => [
            0, 2
        ],
    ],
    (object)[
        'id' => 41,
        'name' => 'PHP Basics',
        'author' => 'Veselin Karaganev',
        'available' => TRUE,
        'duration' => 30,
        'questions' => [
            (object)[
                'text' => 'Which SQL statement is used to extract data from a database?',
                'options' => ['SELECT', 'OPEN', 'EXTRACT', 'GET'],
            ],
            (object)[
                'text' => 'Which SQL statement is used to insert new data into a database?',
                'options' => ['INSERT NEW', 'INSERT INTO', 'ADD RECORD', 'ADD NEW'],
            ],
        ],
    ],
    (object)[
        'id' => 14,
        'name' => 'JavaScript (Unavailable)',
        'author' => 'Veselin Karaganev',
        'available' => FALSE,
        'duration' => 30,
        'questions' => [
            (object)[
                'text' => 'Which SQL statement is used to extract data from a database?',
                'options' => ['SELECT', 'OPEN', 'EXTRACT', 'GET'],
            ],
            (object)[
                'text' => 'Which SQL statement is used to insert new data into a database?',
                'options' => ['INSERT NEW', 'INSERT INTO', 'ADD RECORD', 'ADD NEW'],
            ],
        ],
    ],
];

class DummyQuizManager implements QuizManager
{
    public function __construct($services)
    {
        $this->db = $services->get('DbContext');
    }

    public function getAvailableQuizzes($limit, $offset = 0)
    {
        global $dummy_quiz_set;
        return \array_filter($dummy_quiz_set, function ($quiz) {
            return $quiz->available === TRUE;
        });
    }

    public function findById($id)
    {
        global $dummy_quiz_set;
        foreach (\array_filter($dummy_quiz_set, function ($quiz) use ($id) {
            return $quiz->id === $id;
        }) as &$value) {
            return $value;
        }
    }
}
