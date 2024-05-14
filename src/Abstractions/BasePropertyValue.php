<?php

namespace MTI\DealerApi\V2\Abstractions;

/**
 * @BasePropertyValue
 *
 *@var protected $PROPERTY_USER_TYPE
 *@var protected $PROPERTY_TYPE
 *@var protected $PROPERTY_ENUM_VALUE
 *@var protected $ID;
 *@var protected $PROPERTY_ID;
 *@var protected $IBLOCK_ELEMENT_ID;
 *@var protected $VALUE_TYPE;
 *@var protected $VALUE_ENUM;
 *@var protected $DESCRIPTION;
 *@var protected $PROPERTY_NAME;
 *@var protected $PROPERTY_CODE;
 *@var protected $MULTIPLE;
 *@var protected $PROPERTY_USER_TYPE_SETTINGS;
 *@var protected $PROPERTY_USER_TYPE_SETTINGS_LIST;
 *@var protected $PROPERTY_FILE_SUBDIR;
 *@var protected $PROPERTY_FILE_FILE_NAME;
 *@var protected $VALUE;
 */
abstract class BasePropertyValue extends BaseProperty
{
  protected $PROPERTY_USER_TYPE;
  protected $PROPERTY_TYPE;
  protected $PROPERTY_ENUM_VALUE;
  protected $ID;
  protected $IBLOCK_ELEMENT_ID;
  protected $VALUE_TYPE;
  protected $VALUE_ENUM;
  protected $DESCRIPTION;
  protected $PROPERTY_USER_TYPE_SETTINGS;
  protected $PROPERTY_USER_TYPE_SETTINGS_LIST;
  protected $PROPERTY_FILE_SUBDIR;
  protected $PROPERTY_FILE_FILE_NAME;
  protected $VALUE;

  public function set($name, $value): void
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

  public function getValue()
  {
    return $this->VALUE;
  }
}
