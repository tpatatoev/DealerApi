<?php

namespace MTI\DealerApi\V2\Abstractions;

use MTI\DealerApi\V2\Interfaces\IBaseProduct;
use stdClass;

/**
 * Product
 */
abstract class BaseProduct extends stdClass implements IBaseProduct
{
  // protected $ID;
  // protected $IBLOCK_ID;
  protected $XML_ID;
  protected $NAME;
  // protected $IBLOCK_SECTION_ID;
  protected $SECTION_XML_ID;
  protected $DETAIL_PICTURE_FILE;
  protected $PREVIEW_TEXT;
  protected $DETAIL_TEXT;
  protected $CREATED_DATE;
  protected $TIMESTAMP_X;
  protected $WEIGHT;
  protected $WIDTH;
  protected $LENGTH;
  protected $HEIGHT;
  protected BasePropertyCollection $PROPERTIES;
  protected const DETAIL_PICTURE = "DETAIL_PICTURE_FILE";
  public const PROPERTIES_FIELD = "PROPERTIES";
  protected const XML_ID_FIELD = "XML_ID";
  protected const SECTION_XML_ID_FIELD = "SECTION_XML_ID";
  protected const DETAIL_TEXT_FIELD = "DETAIL_TEXT";
  protected const PREVIEW_TEXT_FIELD = "PREVIEW_TEXT";

  public function __set($name, $value)
  {
  }

  public function __get($name)
  {
  }


  public function set($name, $value)
  {
    if ($name === static::PREVIEW_TEXT_FIELD || $name === static::DETAIL_TEXT_FIELD) {
      $value = htmlspecialchars($value);
    }
    if ($name <> static::PROPERTIES_FIELD) {
      $value = (string) $value;
    }
    $this->$name = $value;
  }

  public function getFields(): array
  {
    return array_keys(get_class_vars(static::class));
  }

  protected function isNotProductField(string $field): bool
  {
    return !in_array($field,  [static::PROPERTIES_FIELD, static::XML_ID_FIELD, static::SECTION_XML_ID_FIELD]);
  }

  public function getProductFields(): array
  {
    return array_filter($this->getFields(), fn ($field) => $this->isNotProductField($field));
  }

  public function get($name)
  {
    return $this->$name;
  }
}
