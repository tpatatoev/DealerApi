<?php

namespace MTI\DealerApi\V2\Factories;

use MTI\DealerApi\V2\Models\PropertyCollection;

class PropertiesCollectionFactory
{
  public static function fromArray(array $arProperties)
  {
    $collection = [];
    foreach ($arProperties as $property) {
      foreach ($property as $propertyValue) {
        $collection[] = PropertiesFactory::fromArray($propertyValue);
      }
    }
    return new PropertyCollection(...$collection);
  }
}
