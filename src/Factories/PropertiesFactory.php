<?php

namespace MTI\DealerApi\V2\Factories;

use MTI\DealerApi\V2\Models\PropertyValue;

/**
 * PropertiesFactory
 */
class PropertiesFactory
{
  public static function fromArray(array $array): PropertyValue
  {
    $property = new PropertyValue;
    foreach ($property->getFields() as $key) {
      $property->set($key, $array[$key]);
    }
    return $property;
  }
}
