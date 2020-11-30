<?php

class UserManager
{
    /**
     * @var DbContext
     */
    private $db;

    public function __construct($services)
    {
        $this->db = $services->get('DbContext');

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

    public function register($email, $password)
    {
        $hash = $this->hash($password);
        $query = $this->db->buildQuery(
            "
            INSERT INTO user (email, password)
            VALUES (':email', ':password')
            ",
            ['email' => $email, 'password' => $hash]
        );

        if ($this->db->getConnection()->query($query) !== TRUE) {
            throw new \Exception("Failed to register $email! Reason: " . $this->db->getConnection()->error);
        }
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

        if ($result = $this->db->getConnection()->query($query)) {
            $row = $result->fetch_assoc();
            if (!$row) {
                throw new \Exception('User not found');
            }
            if (!$includePassword) {
                unset($row['password']);
            }
            $result->close();
            return (object)$row;
        }
        throw new \Exception('Connection to DB reset!');
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
            return $this->findById($_SESSION['userid']);
        }
        return false;
    }

    private function hash($password)
    {
        return \password_hash($password, PASSWORD_BCRYPT);
    }
}
