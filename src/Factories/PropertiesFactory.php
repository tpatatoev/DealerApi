<?php

namespace MTI\DealerApi\V2\Factories;

use MTI\DealerApi\V2\Interfaces\IBaseEntity;
use MTI\DealerApi\V2\Models\Property;
use MTI\DealerApi\V2\Models\PropertyValue;

/**
 * PropertiesFactory
 */
class PropertiesFactory
{
  public static function fromArray(array $arValues): IBaseEntity
  {
    $property = array_key_exists("VALUE", $arValues) ? 
    new PropertyValue : new Property;
    return static::make($property, $arValues);
  }


  
  public static function make(IBaseEntity $baseEntity, $arValues): IBaseEntity
  {
    foreach ($baseEntity->getFields() as $key) {
      $baseEntity->set($key, $arValues[$key]);
    }
    return $baseEntity;
  }
}
