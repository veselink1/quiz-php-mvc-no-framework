<?php

class DbContext
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function getConnection()
    {
        return $this->mysqli;
    }

    public function buildQuery($sql, $params)
    {
        foreach($params as $k => $v) {
            $escaped = $this->mysqli->real_escape_string($v);
            $sql = str_replace(':' . $k, $escaped, $sql);
        }
        return preg_replace('/\s+/', ' ', $sql);
    }
}
