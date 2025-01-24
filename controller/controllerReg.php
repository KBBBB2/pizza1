<?php
require_once '../model/modelUsers.php';
require_once '../service/serviceReg.php';
require_once '../service/serviceValid.php';


class UserRegistration {
    public $data;
    public $serviceValid;
    public $modelUsers = NULL;
    public $errors = [];



    public function __construct($postData) {
        $this->data = $postData;
        $this->serviceValid = new ServiceCheck(
            NULL, 
            $postData['name'], 
            $postData['password'],
            $postData['password2'],
            $postData['email']
        );
    }
    public function conRegist($data) {
        /*
        $this->errors = array_merge(
            $this->errors,
            ServiceCheck::usernameCheck($this->data),
            ServiceCheck::emailCheck($this->data)
        );*/

        /*
        $usernamecheck = new UsernameCheck();

        $result = $usernamecheck->usernameCheck();
        return $result;
        */

        $class1 = new ServiceCheck();
        $class2 = new ModelUsers();

        $class1->usernameCheck();
        $class1->emailCheck();
        $result = $class2->register($this->data); //kiszedtem a data-t 

        if($class1->usernameCheck() == true && $class1->emailCheck() == true) {
            $this->modelUsers = new ModelUsers(
                $data = NULL,
                $data = NULL,
                $data = NULL,
                $data = NULL,

            );
        }

        //lehet array-be kell rakni, és majd itt kell megcsinálni, hogy ez a funkció működjön
    }
}
?>