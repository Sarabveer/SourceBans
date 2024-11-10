<?php

class NormalAuthHandler
{
    private $result = false;

    public function __construct(
        private $dbs,
        string $username, string $password, bool $remember
    )
    {
        $this->dbs = $dbs;
        $user = $this->getInfosFromDatabase($username);

        $maxlife = (($remember) ? Config::get('auth.maxlife.remember') : Config::get('auth.maxlife')) * 60;

        if (!$user || empty($password))
            return;

        if (!empty($password) && (!empty($user['password']) || !is_null($user['password']))) {
            if ($this->checkPassword($password, $user['password'])) {
                $this->result = true;
                Auth::login($user['aid'], $maxlife);
            } elseif ($this->legacyPasswordCheck($password, $user['password'])) {
                $this->result = true;
                $this->updatePasswordHash($password, $user['aid']);
                Auth::login($user['aid'], $maxlife);
            }
        }
    }

    public function getResult()
    {
        return $this->result;
    }

    private function checkPassword(string $password, string $hash)
    {
        return (bool)(password_verify($password, $hash));
    }

    private function legacyPasswordCheck(string $password, string $hash)
    {
        $crypt = @crypt($password, SB_NEW_SALT);
        $sha1 = @sha1(sha1('SourceBans' . $password));

        return (bool)(hash_equals($crypt, $hash) || hash_equals($sha1, $hash));
    }

    private function updatePasswordHash(string $password, int $aid)
    {
        $query = "UPDATE ".DB_PREFIX."_admins SET password = ? WHERE aid = ?";
        $stmt = $this->dbs->Prepare($query);

        if (!$stmt) {
            error_log("Failed to prepare SQL query for updating password: " . implode(" ", $this->dbs->errorInfo()));
            return false;
        }

        $result = $this->dbs->Execute($stmt, array(password_hash($password, PASSWORD_BCRYPT), $aid));

        if (!$result) {
            error_log("Failed to execute SQL query for updating password: " . implode(" ", $this->dbs->errorInfo()));
            return false;
        }

        return true;
    }

    private function getInfosFromDatabase(string $username)
    {
        $query = "SELECT aid, password FROM ".DB_PREFIX."_admins WHERE user = ?";
        $stmt = $this->dbs->Prepare($query);

        if (!$stmt) {
            error_log("Failed to prepare SQL query: " . implode(" ", $this->dbs->errorInfo()));
            return false;
        }

        $result = $this->dbs->Execute($stmt, array($username));

        if (!$result) {
            error_log("Failed to execute SQL query: " . implode(" ", $this->dbs->errorInfo()));
            return false;
        }

        return $result->fetchRow();
    }
}