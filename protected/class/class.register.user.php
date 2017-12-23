<?php

/**
 * Created by PhpStorm.
 * User: DNjunge
 * Date: 12/13/2017
 * Time: 9:28 PM
 */

Doo::loadClass('class.model');
Doo::loadClass('class.escape');


class RegisterUser {
    private $escapeObj;
    private $id;

    private $name;
    private $username;
    private $email;
    private $password;
    private $gender;
    private $birthday = '01/01/1990';
    private $location = '';
    private $hometown = '';
    private $about = '';
    private $facebookId = '';
    private $googleId = '';
    private $twitterId = '';
    private $instagramId = '';

    private $allowedGenders = array('male', 'female');

    function __construct()
    {
        $this->escapeObj = new \QB\Escape();
        return $this;
    }

    public function register()
    {
        if (! empty ($this->name) && ! empty ($this->username) && ! empty ($this->email) && ! empty ($this->password) && ! empty ($this->gender))
        {
            $query = Model::ExecQuery("INSERT INTO " . DB_ACCOUNTS . " (active,about,cover_id,email,email_verification_key,name,password,time,type,username) VALUES (1,'" . $this->about . "',0,'" . $this->email . "','" . md5(generateKey()) . "','" . $this->name . "','" . $this->password . "'," . time() . ",'user','" . $this->username . "')");

            if ($query)
            {
                $defaultPlanQuery = Model::ExecQuery("SELECT id FROM " . DB_SUBSCRIPTION_PLANS . " WHERE is_default=1");
                if ($defaultPlanQuery->num_rows == 1)
                {
                    $defaultPlan = $defaultPlanQuery;

                    $values = array(
                        "id" => $this->id,
                        "birthday" => "'" . $this->birthday . "'",
                        "gender" => "'" . $this->gender . "'",
                        "current_city" => "'" . $this->location . "'",
                        "hometown" => "'" . $this->hometown . "'",
                        "social_login_facebook" => "'" . $this->facebookId . "'",
                        "social_login_google" => "'" . $this->googleId . "'",
                        "social_login_twitter" => "'" . $this->twitterId . "'",
                        "social_login_instagram" => "'" . $this->instagramId . "'",
                        "ip_address" => "'" . getRealIp() . "'",
                        "subscription_plan" => $defaultPlan['id']
                    );
                    $fields = implode(',', array_keys($values));
                    $values = implode(',', $values);

                    $query2 = Model::ExecQuery("INSERT INTO " . DB_USERS . " ($fields) VALUES ($values)");

                    if ($query2)
                    {
//                        $timelineObj = new \SocialKit\User();
//                        $timelineObj->setId($this->id);
//                        $get = $timelineObj->getRows();
//
                        return true;
                    }
                }
            }
        }
    }

    private function validateUsername($u)
    {
        if (strlen($u) > 3 && ! is_numeric($u) && preg_match('/^[A-Za-z0-9_]+$/', $u))
        {
            return true;
        }
    }

    public function setName($n)
    {
        if (! empty($n))
        {
            $this->name = $this->escapeObj->stringEscape($n);
        }
    }

    public function setUsername($u)
    {
        if ($this->validateUsername($u))
        {
            $this->username = $this->escapeObj->stringEscape($u);
        }
    }

    public function setEmail($e)
    {
        if (filter_var($e, FILTER_VALIDATE_EMAIL))
        {
            $this->email = $this->escapeObj->stringEscape($e);
        }
    }

    public function setPassword($p)
    {
        if (! empty($p))
        {
            $this->password = md5( trim($p) );
        }
    }

    public function setGender($g)
    {
        if (in_array($g, $this->allowedGenders))
        {
            $this->gender = $g;
        }
    }

    public function setBirthday($b)
    {
        if (is_array($b))
        {
            $b = implode('/', $b);
            $regex = '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/';

            if (preg_match($regex, $b))
            {
                $this->birthday = $b;
            }
        }
    }

    public function setLocation($l)
    {
        if (! empty($l))
        {
            $this->location = $this->escapeObj->stringEscape($l);
        }
    }

    public function setHometown($h)
    {
        if (! empty($h))
        {
            $this->hometown = $this->escapeObj->stringEscape($h);
        }
    }

    public function setAbout($a)
    {
        if (! empty($a))
        {
            $this->about = $this->escapeObj->stringEscape($a);
        }
    }

    public function setFacebookId($fbid)
    {
        if (!empty($fbid)) $this->facebookId = $this->escapeObj->stringEscape($fbid);
    }

    public function setGoogleId($gid)
    {
        if (!empty($gid)) $this->googleId = $this->escapeObj->stringEscape($gid);
    }

    public function setTwitterId($twid)
    {
        if (!empty($twid)) $this->twitterId = $this->escapeObj->stringEscape($twid);
    }

    public function setInstagramId($insid)
    {
        if (!empty($insid)) $this->instagramId = $this->escapeObj->stringEscape($insid);
    }
}