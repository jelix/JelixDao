<?php

abstract class Post extends \Jelix\Dao\AbstractDaoRecord {

    function getAuthorObject()
    {
        if ($this->author) {
            $user = new stdClass();
            $user->name = $this->author;
            return $user;
        } else {
            return NULL;
        }
    }

}
