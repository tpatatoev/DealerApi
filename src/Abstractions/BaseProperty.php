<?php

namespace MTI\DealerApi\V2\Abstractions;

use MTI\DealerApi\V2\Interfaces\IBaseProperty;
use stdClass;

/**
 * ItemProperty
 */
abstract class BaseProperty extends stdClass implements IBaseProperty
{
  protected $PROPERTY_USER_TYPE;
  protected $PROPERTY_TYPE;
  protected $PROPERTY_ENUM_VALUE;
  protected $ID;
  protected $IBLOCK_PROPERTY_ID;
  protected $IBLOCK_ELEMENT_ID;
  protected $VALUE_TYPE;
  protected $VALUE_ENUM;
  protected $DESCRIPTION;
  protected $PROPERTY_NAME;
  protected $PROPERTY_CODE;
  protected $PROPERTY_MULTIPLE;
  protected $PROPERTY_USER_TYPE_SETTINGS;
  protected $PROPERTY_USER_TYPE_SETTINGS_LIST;
  protected $PROPERTY_FILE_SUBDIR;
  protected $PROPERTY_FILE_FILE_NAME;
  protected $VALUE;
  protected const CONVERTION = ["S:HTML" => "HTML", "S:directory" => "L", "S:DateTime" => "S", "S:Date" => "S"];
  protected const PROPERTY_USER_TYPE = "PROPERTY_USER_TYPE";
  protected const PROPERTY_VALUE = "VALUE";


  public function __set($name, $value)
  {
  }

  public function __get($name)
  {
  }


  public function set($name, $value)
  {
    if ($name === static::PROPERTY_VALUE) {
      switch ($this->getType()) {
        case 'L':
          $value = $this->PROPERTY_ENUM_VALUE;
          break;
        case 'S:HTML':
          $value = htmlspecialcharsbx(unserialize($value)["TEXT"]);
          break;
        default:
          $value = $value;
          break;
      }
    }
    $this->$name = $value;
  }

  public function getFields(): array
  {
    return array_keys(get_class_vars(static::class));
  }

  public function getType(): string
  {
    return $this->isComplexType() ?
      $this->getComplexType() : $this->PROPERTY_TYPE;
  }

  public function isComplexType()
  {
    return $this->PROPERTY_USER_TYPE ? true : false;
  }

  protected function getComplexType()
  {
    return  $this->PROPERTY_TYPE . ":" . $this->PROPERTY_USER_TYPE;
  }

  public function getValue()
  {
    return $this->VALUE;
  }
}
