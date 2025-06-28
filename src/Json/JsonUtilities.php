<?php

namespace Jelix\Dao\Json;

class JsonUtilities
{
    static function encodeUsingExternalObjectMethod($class, $method, $data)
    {
        $obj = new $class();
        return $obj->{$method}($data);
    }

    static function decodeToNewObjectUsingMethod($class, $method, $jsonString)
    {
        $obj = new $class();
        return $obj->{$method}($jsonString);
    }

    static function decodeToNewObject($class, $jsonString)
    {
        $data = json_decode($jsonString);
        $obj = new $class();
        foreach ($data as $property => $value) {
            $obj->{$property} = $value;
        }
        return $obj;
    }
}