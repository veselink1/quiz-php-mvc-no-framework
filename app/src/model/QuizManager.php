<?php

require_once __DIR__ . '/RecordManager.php';

function QM_toObject($array) {
    return (object)$array;
}

class QuizManager extends RecordManager
{
    public function __construct($services)
    {
        parent::__construct($services->get('DbContext'));
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

        return $this->queryAll($query);
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

        return $this->execute($query);
    }

    public function updateQuiz($quiz)
    {
        $query = $this->db->buildQuery(
            "
            UPDATE quiz
            SET
                title = ':title',
                duration = :duration,
                available = :available
            WHERE quiz.id = :id;
            ",
            ['id' => $quiz->id, 'title' => $quiz->title, 'duration' => $quiz->duration, 'available' => $quiz->available]
        );

        return $this->execute($query);
    }

    public function deleteQuiz($quiz)
    {
        $query = $this->db->buildQuery(
            "
            DELETE FROM quiz
            WHERE quiz.id = :id;
            ",
            ['id' => $quiz->id]
        );

        $this->execute($query);
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

        return $this->queryOne($query, function($row) use ($id) {
            $row['questions'] = $this->getQuestions($id);
            return (object)$row;
        });
    }

    public function getSubmission($user, $quiz)
    {
        $query = $this->db->buildQuery(
            "
            SELECT s.date_of_attempt, ans.question_no, ans.answer as submitted,
                (SELECT question.answer FROM question WHERE question.quiz_id = :quiz_id AND question.no = ans.question_no) as answer
            FROM submission s
            INNER JOIN submitted_answer ans
                ON ans.user_id = s.user_id AND ans.quiz_id = s.quiz_id
            WHERE s.user_id = :user_id AND s.quiz_id = :quiz_id
            ",
            ['user_id' => $user->id, 'quiz_id' => $quiz->id]
        );

        $date = null;
        $responses = [];

        $this->queryAll($query, function($row) use (&$date, &$responses) {
            $date = $row['date_of_attempt'];
            $responses[$row['question_no']] = (object)[
                'submitted' => $row['submitted'],
                'answer' => $row['answer'],
            ];
        });

        if ($date === null) {
            return false;
        }

        return (object)[
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'date_of_attempt' => $date,
            'responses' => $responses,
        ];
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

        return $this->queryAll($query, function($row) use ($id) {
            return (object)[
                'text' => $row['text'],
                'answer' => $row['answer'],
                'no' => $row['no'],
                'quiz_id' => $id,
                'options' => [$row['opt_a'], $row['opt_b'], $row['opt_c'], $row['opt_d']],
            ];
        });
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

        return $this->queryAll($query);
    }

    public function addSubmission($user, $quiz, $responses)
    {
        $query = $this->db->buildQuery(
            "
            INSERT INTO submission (user_id, quiz_id, date_of_attempt)
                VALUES (:user_id, :quiz_id, NOW());
            ",
            ['user_id' => $user->id, 'quiz_id' => $quiz->id]
        );

        foreach ($responses as $question_no => $answer) {
            $query .= $this->db->buildQuery(
                "
                INSERT INTO submitted_answer (user_id, quiz_id, question_no, answer)
                    VALUES (:user_id, :quiz_id, :question_no, :answer);
                ",
                ['user_id' => $user->id, 'quiz_id' => $quiz->id, 'question_no' => $question_no, 'answer' => $answer]
            );
        }

        if ($this->db->getConnection()->multi_query($query) !== TRUE) {
            throw new \Exception('Answer not recorded! Reason: ' . $this->db->getConnection()->error);
        }
        return $this->db->getConnection()->insert_id;
    }

    public function addQuestion($quiz, $no, $text, $answer, $options)
    {
        $query = $this->db->buildQuery(
            "
            INSERT INTO question (quiz_id, no, text, answer, opt_a, opt_b, opt_c, opt_d)
                VALUES (:quiz_id, :no, ':text', :answer, ':opt_a', ':opt_b', ':opt_c', ':opt_d')
            ",
            [
                'quiz_id' => $quiz->id, 'no' => $no,
                'text' => $text, 'answer' => $answer,
                'opt_a' => $options[0],
                'opt_b' => $options[1],
                'opt_c' => $options[2],
                'opt_d' => $options[3],
            ]
        );

        return $this->execute($query);
    }

    public function updateQuestion($quiz, $no, $question)
    {
        $query = $this->db->buildQuery(
            "
            UPDATE question
            SET
                no = :no,
                text = ':text',
                answer = :answer,
                opt_a = ':opt_a',
                opt_b = ':opt_b',
                opt_c = ':opt_c',
                opt_d = ':opt_d'
            WHERE
                quiz_id = :quiz_id AND no = :orig_no
            ",
            [
                'quiz_id' => $quiz->id,
                'orig_no' => $no,
                'no' => $question->no,
                'text' => $question->text,
                'answer' => $question->answer,
                'opt_a' => $question->options[0],
                'opt_b' => $question->options[1],
                'opt_c' => $question->options[2],
                'opt_d' => $question->options[3],
            ]
        );

        return $this->execute($query);
    }

    public function findQuestion($quiz, $no)
    {
        $query = $this->db->buildQuery(
            "
            SELECT q.text, q.answer, q.opt_a, q.opt_b, q.opt_c, q.opt_d
            FROM question q
            WHERE q.quiz_id = :quiz_id AND q.no = :no
            ",
            ['quiz_id' => $quiz->id, 'no' => $no]
        );

        return $this->queryOne($query, function($row) use (&$quiz, $no) {
            return [
                'quiz_id' => $quiz->id,
                'no' => $no,
                'text' => $row['text'],
                'answer' => $row['answer'],
                'options' => [
                    $row['opt_a'],
                    $row['opt_b'],
                    $row['opt_c'],
                    $row['opt_d'],
                ],
            ];
        });
    }

    public function deleteQuestion($quiz, $no)
    {
        $query = $this->db->buildQuery(
            "
            DELETE FROM question
            WHERE quiz_id = :quiz_id AND no = :no;
            ",
            ['quiz_id' => $quiz->id, 'no' => $no]
        );

        $this->execute($query);
    }
}
