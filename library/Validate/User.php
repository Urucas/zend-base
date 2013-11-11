<?php
class Validate_User extends Zend_Validate_Abstract {

    public function isValid($user) {
        return preg_match("/^[a-zA-z0-9_]{5,15}$/", $user);
    }
}
?>
