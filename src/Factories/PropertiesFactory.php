<?php

namespace MTI\DealerApi\V2\Factories;

use MTI\DealerApi\V2\Models\Property;

/**
 * PropertiesFactory
 */
class PropertiesFactory
{
  public static function fromArray(array $array): Property
  {
    $property = new Property;
    foreach ($property->getFields() as $key) {
      $property->set($key, $array[$key]);
    }
    return $property;
  }
}
