<?php

namespace MTI\DealerApi\V2\Abstractions;

use MTI\DealerApi\V2\Interfaces\IBaseEntity;
use stdClass;

/**
 * BaseProperty
 */
abstract class BaseEntity extends stdClass implements IBaseEntity
{
  protected $PROPERTY_USER_TYPE;
  protected $PROPERTY_TYPE;
  protected const CONVERTION = ["S:HTML" => "S:HTML", "S:directory" => "L", "S:DateTime" => "S", "S:Date" => "S"];
  protected const PROPERTY_USER_TYPE = "PROPERTY_USER_TYPE";
  protected const PROPERTY_VALUE = "VALUE";
  protected const PROPERTY_TYPE = "PROPERTY_TYPE";


  public function __set($name, $value)
  {
  }

  public function __get($name)
  {
  }


  abstract public function set($name, $value): void;


  public function getFields(): array
  {
    return array_keys(get_class_vars(static::class));
  }


  public function isComplexType(): bool
  {
    return $this->PROPERTY_USER_TYPE ? true : false;
  }


  public function getType($convert = true): string
  {
    return $this->isComplexType() ?
      $this->getComplexType($convert) : $this->PROPERTY_TYPE;
  }

  public function getXmlType()
  {
    return $this->getType(false);
  }

  protected function getComplexType($convert = true): string
  {
    $tempValue = $this->PROPERTY_TYPE . ":" . $this->PROPERTY_USER_TYPE;

    if ($convert)
      return  in_array($tempValue, array_keys(static::CONVERTION)) ? $this->toConverted($tempValue) : $tempValue;
    return $tempValue;
  }

  protected function toConverted($value)
  {
    return static::CONVERTION[$value];
  }
}
