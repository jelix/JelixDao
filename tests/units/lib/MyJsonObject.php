<?php

namespace Jelix\DaoTests;

class MyJsonObject
{
    public $id;
    public $name;

    /*
    public function toJsonString()
    {
        return json_encode(['theid'=>$this->id, 'thename'=>$this->name]);
    }
    */

    public static function staticToJsonString(MyJsonObject $object)
    {
        return json_encode(['theid'=>$object->id, 'thename'=>$object->name], JSON_FORCE_OBJECT);
    }

    public static function createFromJson($json)
    {
        $obj = json_decode($json);
        $o = new MyJsonObject();
        $o->id = $obj->theid;
        $o->name = $obj->thename;
        return $o;
    }
}

