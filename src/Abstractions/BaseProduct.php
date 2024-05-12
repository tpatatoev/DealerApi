<?php

namespace MTI\DealerApi\V2\Abstractions;

use MTI\DealerApi\V2\Interfaces\IBaseProduct;
use stdClass;

/**
 * Product
 */
abstract class BaseProduct extends stdClass implements IBaseProduct
{
  protected $ID;
  protected $IBLOCK_ID;
  protected $XML_ID;
  protected $NAME;
  protected $IBLOCK_SECTION_ID;
  protected $SECTION_XML_ID;
  protected $DETAIL_PICTURE;
  protected $PREVIEW_TEXT;
  protected $DETAIL_TEXT;
  protected $CREATED_DATE;
  protected $TIMESTAMP_X;
  protected $WEIGHT;
  protected $WIDTH;
  protected $LENGTH;
  protected $HEIGHT;
  protected BasePropertyCollection $PROPERTIES;
  private const DETAIL_PICTURE = "DETAIL_PICTURE";
  public const PROPERTIES_FIELD = "PROPERTIES";
  private const DETAIL_TEXT_FIELD = "DETAIL_TEXT";
  private const PREVIEW_TEXT_FIELD = "PREVIEW_TEXT";

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
}
