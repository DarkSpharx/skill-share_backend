<?php

declare(strict_types=1);

namespace App\model;

use DateTime;

class User
{
    private int $iduser;
    private string $username;
    // avatar facultatif, si non renseigné, on mettra une image par défaut
    private string $avatar = "avatar-default.png";
    private string $email;
    private string $password;
    private array $role = ["ROLE_USER"];
    private string $createdAt;
    private ?string $email_token;
    private bool $is_verified;
    private string $verified_at;

    public function __construct(array $data)
    {
        $this->username = $data["username"];
        $this->avatar = $data["avatar"] ?? $this->avatar;
        $this->email = $data["email"];
        $this->password = $data["password"];
        $this->email_token = $data["email_token"];
        $this->is_verified = isset($data["is_verified"]) ? (bool)$data["is_verified"] : false;

        if (isset($data["role"])) {
            if (is_array($data["role"])) {
                $this->role = $data["role"];
            } else {
                // Si c'est une string JSON, on la décode
                $decoded = json_decode($data["role"], true);
                $this->role = is_array($decoded) ? $decoded : [$data["role"]];
            }
        }
    }

    /**
     * Get the value of id
     */
    public function getIduser()
    {
        return $this->iduser;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setIduser($iduser)
    {
        $this->iduser = $iduser;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of avatar
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set the value of avatar
     *
     * @return  self
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of email_token
     */
    public function getEmail_token()
    {
        return $this->email_token;
    }

    /**
     * Set the value of email_token
     *
     * @return  self
     */
    public function setEmail_token(?string $email_token)
    {
        $this->email_token = $email_token;

        return $this;
    }

    /**
     * Get the value of is_verified
     */
    public function getIs_verified()
    {
        return $this->is_verified;
    }

    /**
     * Set the value of is_verified
     *
     * @return  self
     */
    public function setIs_verified($is_verified)
    {
        $this->is_verified = $is_verified;

        return $this;
    }

    /**
     * Get the value of verified_at
     */
    public function getVerified_at()
    {
        return $this->verified_at;
    }

    /**
     * Set the value of verified_at
     *
     * @return  self
     */
    public function setVerified_at($verified_at)
    {
        $this->verified_at = $verified_at;

        return $this;
    }
}
