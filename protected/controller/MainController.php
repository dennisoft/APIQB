<?php

Doo::loadClass('class.model');

/**
 * MainController
 * Feel free to delete the methods and replace them with your own code.
 */
class MainController extends DooController
{

    protected $content;
    protected $decoded;

    public function beforeRun($resource, $action)
    {

        //validate request
        //Make sure that it is a POST request.
        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
            throw new Exception('Request method must be POST!');
        }

        //Make sure that the content type of the POST request has been set to application/json
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if (strcasecmp($contentType, 'application/json') != 0) {
            throw new Exception('Content type must be: application/json');
        }

        //Receive the RAW post data.
        $this->content = trim(file_get_contents("php://input"));

        //Attempt to decode the incoming RAW post data from JSON.
        $this->decoded = json_decode($this->content, true);

        //If json_decode failed, the JSON is invalid.
        if (!is_array($this->decoded)) {
            throw new Exception('Received content contained invalid JSON!');
        }
    }

    public function afterRun($routeResult)
    {
        parent::afterRun($routeResult);
        $this->toJSON($this->result, true);
    }

    public function index()
    {
        $this->result['message'] = "QBAPI";
    }

    public function changePassword() {
        $username = $this->decoded['user'];
        $oldPassword = $this->decoded['oldpassword'];
        $newPassword = $this->decoded['newpassword'];
        $repeatPassword = $this->decoded['repeatpassword'];

        if (!isset($username)) {
            $this->result['code'] = '400';
            $this->result['desc'] = 'Missing Username';
        } else if (!isset($oldPassword)) {
            $this->result['code'] = '400';
            $this->result['desc'] = 'Missing Old Password';
        } else if (!isset($newPassword)) {
            $this->result['code'] = '400';
            $this->result['desc'] = 'Missing new Password';
        }else if (!isset($repeatPassword)) {
            $this->result['code'] = '400';
            $this->result['desc'] = 'Missing Repeat Password';
        }else if ($newPassword != $repeatPassword) {
            $this->result['code'] = '400';
            $this->result['desc'] = 'Repeat password mismatch';
        }else {

            $sql = "SELECT * FROM accounts WHERE username = '$username'";

            try {
                $execQuery = Model::ExecQuery($sql);

                if (isset($execQuery) && count($execQuery) == 0) {
                    $this->result['code'] = '404';
                    $this->result['desc'] = 'This user does not exist';
                } else {
                    $newPassword = md5($newPassword);
                    $oldPassword = md5($oldPassword);
                    $login = "SELECT * FROM accounts WHERE username = '$username' AND password = '$oldPassword'";
                    try {
                        $execQuery = Model::ExecQuery($login);

                        if (isset($execQuery) && count($execQuery) > 0) {
                            $change = "UPDATE accounts SET password = '$newPassword' WHERE username = '$username' AND password = '$oldPassword'";
                            $execUpdate = Model::ExecNonQuery($change);

                            if (isset($execUpdate)) {
                                $this->result['code'] = '200';
                                $this->result['desc'] = 'Success';
                                $this->result['detailed'] = 'Password changed successfully';
                            } else {
                                $this->result['code'] = '404';
                                $this->result['desc'] = 'Change failed. Contact SystemAdmin';
                            }

                        } else if (isset($execQuery) && count($execQuery) == 0) {
                            $this->result['code'] = '404';
                            $this->result['desc'] = 'Wrong Password';
                        } else {
                            $this->result['code'] = '404';
                            $this->result['desc'] = 'Wrong Credentials';
                        }

                    } catch (Exception $e) {
                        $this->result['error']['code'] = '500';
                        $this->result['error']['desc'] = 'Internal Server Error';
                    }
                }

            } catch (Exception $e) {
                $this->result['error']['code'] = '500';
                $this->result['error']['desc'] = 'Internal Server Error';
            }
        }
    }

    public function auth()
    {
        //$content = trim(file_get_contents("php://input"));
        //$decoded = json_decode($this->content, true);
        $username = $this->decoded['user'];
        $password = $this->decoded['password'];

        if (!isset($username)) {
            $this->result['code'] = '400';
            $this->result['desc'] = 'Missing Username';
        } else if (!isset($password)) {
            $this->result['code'] = '400';
            $this->result['desc'] = 'Missing Password';
        } else {

            $sql = "SELECT * FROM accounts WHERE username = '$username'";

            try {
                $execQuery = Model::ExecQuery($sql);

                if (isset($execQuery) && count($execQuery) == 0) {
                    $this->result['code'] = '404';
                    $this->result['desc'] = 'This user does not exist';
                } else {
                    $password = md5($password);
                    $login = "SELECT * FROM accounts WHERE username = '$username' AND password = '$password'";
                    try {
                        $execQuery = Model::ExecQuery($login);

                        if (isset($execQuery) && count($execQuery) > 0) {
                            $this->result['code'] = '200';
                            $this->result['desc'] = 'Success';
                            $this->result['user'] = $execQuery;
                        } else if (isset($execQuery) && count($execQuery) == 0) {
                            $this->result['code'] = '404';
                            $this->result['desc'] = 'Wrong Password';
                        } else {
                            $this->result['code'] = '404';
                            $this->result['desc'] = 'Wrong Credentials';
                        }

                    } catch (Exception $e) {
                        $this->result['error']['code'] = '500';
                        $this->result['error']['desc'] = 'Internal Server Error';
                    }
                }

            } catch (Exception $e) {
                $this->result['error']['code'] = '500';
                $this->result['error']['desc'] = 'Internal Server Error';
            }
        }
    }
}
?>