<?php

namespace Jelix\DaoTests;

class MyJsonSerializer
{

    public function toJsonString2(MyJsonObject $object)
    {
        return json_encode(['theid'=>$object->id, 'thename'=>$object->name]);
    }

    public static function toJsonString(MyJsonObject $object)
    {
        return json_encode(['theid2'=>$object->id, 'thename2'=>$object->name], JSON_FORCE_OBJECT);
    }

    public static function createFromJson($json)
    {
        $obj = json_decode($json);
        $o = new MyJsonObject();
        $o->id = $obj->theid2;
        $o->name = $obj->thename2;
        return $o;
    }
}

