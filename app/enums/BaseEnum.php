<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 10:14 PM
 */

namespace app\enums;


use ReflectionClass;

class BaseEnum
{
    public static function fromString($label)
    {
        $value = null;
        $reflector = new ReflectionClass(get_called_class());
        if ($reflector->hasConstant($label))
        {
            $value = constant(get_called_class() . '::' . $label);
        }
        return $value;
    }

    public static function label($enumValue)
    {
        $result = 'IllegalValue';
        $reflector = new ReflectionClass(get_called_class());

        foreach($reflector->getConstants() as $key => $val)
        {
            if ($val == $enumValue)
            {
                $result = $key;
                break;
            }
        }
        return $result;
    }

    public static function getAllValues()
    {
        $reflector = new ReflectionClass(get_called_class());
        return $reflector->getConstants();
    }

    public static function getAllValuesAsIdValueObjects()
    {
        $objects = [];

        $reflector = new ReflectionClass(get_called_class());
        $values = $reflector->getConstants();

        foreach($values as $name => $id)
        {
            $objects[] = [
                'id' => $id,
                'name' => $name
            ];
        }

        return $objects;
    }
}