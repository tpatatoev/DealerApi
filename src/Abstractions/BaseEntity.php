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
  protected const CONVERTION = ["S:HTML" => "HTML", "S:directory" => "L", "S:DateTime" => "S", "S:Date" => "S"];
  protected const PROPERTY_USER_TYPE = "PROPERTY_USER_TYPE";
  protected const PROPERTY_VALUE = "VALUE";


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

  public function getType(): string
  {
    return $this->isComplexType() ?
      $this->getComplexType() : $this->PROPERTY_TYPE;
  }

  public function isComplexType(): bool
  {
    return $this->PROPERTY_USER_TYPE ? true : false;
  }

  protected function getComplexType(): string
  {
    return  $this->PROPERTY_TYPE . ":" . $this->PROPERTY_USER_TYPE;
  }
}
