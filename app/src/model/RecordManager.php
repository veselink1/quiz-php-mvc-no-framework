<?php

abstract class RecordManager
{

    /**
     * @var DbContext
     */
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    protected function queryOne($query, $transform = 'QM_toObject')
    {
        if ($result = $this->db->getConnection()->query($query)) {
            $row = $result->fetch_assoc();
            if (!$row) {
                throw new \Exception('Record not found');
            }
            $result->close();
            return $transform($row);
        }

        $error = $this->db->getConnection()->error;
        throw new \Exception("Query '$query' failed!\n\tReason: $error");
    }

    protected function queryAll($query, $transform = 'QM_toObject')
    {
        $results = [];
        if ($result = $this->db->getConnection()->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $transform($row);
            }
            $result->close();
            return $results;
        }

        $error = $this->db->getConnection()->error;
        throw new \Exception("Query '$query' failed!\n\tReason: $error");
    }

    protected function execute($query)
    {
        if ($this->db->getConnection()->query($query) !== TRUE) {
            $error = $this->db->getConnection()->error;
            throw new \Exception("Table not updated! Query '$query' failed!\n\tReason: $error");
        }
        return $this->db->getConnection()->insert_id;
    }
}
