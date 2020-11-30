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
        \session_start();
    }
}
