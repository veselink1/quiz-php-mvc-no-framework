<?php

class QuizManager
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
        return $this->getQuizzes($limit, $offset, TRUE);
    }

    public function getUnavailableQuizzes($limit, $offset = 0)
    {
        return $this->getQuizzes($limit, $offset, FALSE);
    }

    public function getAllQuizzes($limit, $offset = 0)
    {
        return $this->getQuizzes($limit, $offset, null);
    }

    public function getQuizzesByAuthor($author, $limit, $offset = 0)
    {
        $query = $this->db->buildQuery(
            "
            SELECT quiz.id, quiz.duration, quiz.title, quiz.available
                FROM quiz
                WHERE quiz.author_id = :author_id
                LIMIT :limit OFFSET :offset
            ",
            ['limit' => $limit, 'offset' => $offset, 'author_id' => $author->id]
        );

        $results = [];
        if ($result = $this->db->getConnection()->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $results[] = (object)$row;
            }
            $result->close();
            return $results;
        }
        throw new \Exception('Connection to DB reset!');
    }

    public function addQuiz($author, $title, $duration, $available)
    {
        $query = $this->db->buildQuery(
            "
            INSERT INTO quiz (author_id, title, duration, available)
                VALUES (:author_id, ':title', :duration, :available)
            ",
            ['author_id' => $author->id, 'title' => $title, 'duration' => $duration, 'available' => $available]
        );

        if ($this->db->getConnection()->query($query) !== TRUE) {
            throw new \Exception('Quiz not created');
        }
        return $this->db->getConnection()->insert_id;
    }

    public function updateQuiz($quiz) {
        $query = $this->db->buildQuery(
            "
            UPDATE quiz
                SET title = ':title',
                    duration = :duration,
                    available = :available
                WHERE quiz.id = :id;
            ",
            ['id' => $quiz->id, 'title' => $quiz->title, 'duration' => $quiz->duration, 'available' => $quiz->available]
        );

        if ($this->db->getConnection()->query($query) !== TRUE) {
            throw new \Exception('Quiz not updated');
        }
    }

    public function findById($id)
    {
        $query = $this->db->buildQuery(
            "
            SELECT quiz.id, quiz.duration, quiz.title, quiz.available, a.name as author, a.id as author_id
                FROM quiz
                INNER JOIN `user` a ON a.id = quiz.author_id
                WHERE quiz.id = :id
            ",
            ['id' => $id]
        );

        if ($result = $this->db->getConnection()->query($query)) {
            $row = $result->fetch_assoc();
            if (!$row) {
                throw new \Exception('Quiz not found');
            }
            $result->close();

            $row['questions'] = $this->getQuestions($id);
            return (object)$row;
        }
        throw new \Exception('Connection to DB reset!');
    }

    public function getQuestions($id)
    {
        $query = $this->db->buildQuery(
            "
            SELECT q.text, q.opt_a, q.opt_b, q.opt_c, q.opt_d, q.no, q.answer
                FROM question q
                INNER JOIN quiz ON quiz.id = q.quiz_id
                WHERE quiz.id = :id
                ORDER BY q.no
            ",
            ['id' => $id]
        );

        $results = [];
        if ($result = $this->db->getConnection()->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $results[] = (object)[
                    'text' => $row['text'],
                    'answer' => $row['answer'],
                    'no' => $row['no'],
                    'options' => [$row['opt_a'], $row['opt_b'], $row['opt_c'], $row['opt_d']],
                ];
            }
            $result->close();
            return $results;
        }
        throw new \Exception('Connection to DB reset!');
    }

    private function getQuizzes($limit, $offset, $availability)
    {
        $condition = '';
        if ($availability === TRUE) {
            $condition = 'WHERE quiz.available = 1';
        } else if ($availability === FALSE) {
            $condition = 'WHERE quiz.available = 0';
        }
        $query = $this->db->buildQuery(
            "
            SELECT quiz.id, quiz.duration, quiz.title, a.name as author
                FROM quiz
                INNER JOIN `user` a ON a.id = quiz.author_id
                $condition
                LIMIT :limit OFFSET :offset
            ",
            ['limit' => $limit, 'offset' => $offset]
        );

        $results = [];
        if ($result = $this->db->getConnection()->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $results[] = (object)$row;
            }
            $result->close();
            return $results;
        }
        throw new \Exception('Connection to DB reset!');
    }
}
