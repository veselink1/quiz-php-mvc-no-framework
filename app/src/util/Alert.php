<?php

class Alert
{
    const SUCCESS = 'success';
    const ERROR = 'danger';

    private $message;
    private $severity;

    public function __construct($message, $severity)
    {
        $this->message = $message;
        $this->severity = $severity;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getSeverity()
    {
        return $this->severity;
    }
}
