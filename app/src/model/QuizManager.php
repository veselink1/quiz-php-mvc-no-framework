<?php

class QuizManager
{
    /**
     * @var mysqli
     */
    private $db;

    public function __construct($dbConnection)
    {
        if ($dbConnection instanceof \mysqli) {
            $this->db = $dbConnection;
        } else {
            throw new \Exception('Argument is not a mysqli object');
        }
        \session_start();
    }
}
