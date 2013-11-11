<?php
class Validate_Password extends Zend_Validate_Abstract {

    public function isValid($pass) {
        return preg_match("/^[a-zA-z0-9]{6,10}$/", $pass);
    }
}
?>
