<?php

require_once __DIR__ . '/RecordManager.php';

class UserManager extends RecordManager
{
    private $lastUserID;
    private $lastUser;

    public function __construct($services)
    {
        parent::__construct($services->get('DbContext'));

        if(!isset($_SESSION)) {
            session_start();
       }
    }

    public function login($email, $password)
    {
        $user = $this->findById($email, true);
        if (\password_verify($password, $user->password)) {
            $_SESSION['userid'] = $user->email;
            return;
        }
        throw new \Exception('Incorrect credentials');
    }

    public function register($email, $password, $name)
    {
        $hash = $this->hash($password);
        $query = $this->db->buildQuery(
            "
            INSERT INTO user (email, name, password)
            VALUES (':email', ':name', ':password')
            ",
            ['email' => $email, 'name' => $name, 'password' => $hash]
        );

        $this->execute($query);
    }

    /**
     * Get user by their ID.
     * @param String $email
     * @return object
     */
    public function findById($email, $includePassword = false)
    {
        $query = $this->db->buildQuery(
            "
            SELECT * FROM user
            WHERE email = ':email'
            LIMIT 1
            ",
            ['email' => $email]
        );

        return $this->queryOne($query, function($row) use ($includePassword) {
            if (!$includePassword) {
                unset($row['password']);
            }
            return (object)$row;
        });
    }

    public function logout()
    {
        unset($_SESSION['userid']);
        return true;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['userid']) && $_SESSION['userid'];
    }

    public function getCurrentUser()
    {
        if ($this->isLoggedIn()) {
            $userID = $_SESSION['userid'];
            if ($userID == $this->lastUserID) {
                return $this->lastUser;
            }
            $this->lastUserID = $userID;
            return $this->lastUser = $this->findById($userID);
        }
        return false;
    }

    private function hash($password)
    {
        return \password_hash($password, PASSWORD_BCRYPT);
    }
}
