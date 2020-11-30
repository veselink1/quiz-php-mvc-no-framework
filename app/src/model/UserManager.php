<?php

class UserManager
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

        if(!isset($_SESSION)) {
            session_start();
       }
    }

    public function login($email, $password)
    {
        $user = $this->findOneUserById($email, $password);
        if ($user) {
            $_SESSION['userid'] = $user['id'];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get user by it's credentials
     * @param String $email
     * @param String $password
     * @return array
     */
    public function findOneUserById($email, $password)
    {
        $query = ""
            . "SELECT user.* "
            . "FROM user "
            . "WHERE user.email = '%s' AND user.password = '%s'";

        $query = \sprintf($query, $this->db->real_escape_string($email), $this->hash($password));
        if ($result = $this->db->query($query)) {
            $row = $result->fetch_assoc();
            if (!$row) {
                return false;
            }
            $user = [
                'id' => $row['id'],
                'email' => $row['email'],
                'name' => $row['name']
            ];
            $result->close();
        } else {
            die($this->db->error);
        }
        return $user;
    }

    public function logout()
    {
        unset($_SESSION['userid']);
        return true;
    }

    public function isLoggedIn()
    {
        if (isset($_SESSION['userid']) && $_SESSION['userid']) return $_SESSION['userid'];
        return false;
    }

    private function hash($password)
    {
        return \hash('sha512', $this->db->real_escape_string($password));
    }
}
