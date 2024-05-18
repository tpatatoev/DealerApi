<?php

namespace MTI\DealerApi\V2\Factories;

use MTI\DealerApi\V2\Models\PropertyCollection;

class PropertiesCollectionFactory
{
  public static function from3DArray(array $arProperties): PropertyCollection
  {
    $collection = [];
    foreach ($arProperties as $entity) {
      $collection = [...$collection, ...static::make($entity)];
    }
    return new PropertyCollection(...$collection);
  }

  public static function fromArray(array $arProperties): PropertyCollection
  {
    return new PropertyCollection(...static::make($arProperties));
  }

  public static function make(array $arEntities)
  {
    $collection = [];
    foreach ($arEntities as $entity) {
      $collection[] = PropertiesFactory::fromArray($entity);
    }
    return $collection;
  }
}
