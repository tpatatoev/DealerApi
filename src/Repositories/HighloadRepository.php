<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Highloadblock\HighloadBlockTable;
use CModule;

CModule::IncludeModule('highloadblock');


class HighLoadRepository extends HighloadBlockTable
{

  protected static array $container = [];

  public static function getValue(array $arParams)
  {
    $arResult = [];
    if (!$arParams["TABLE_NAME"]) return $arResult;

    $hldata = array_pop(static::getList(
      array('filter' => array('TABLE_NAME' => $arParams["TABLE_NAME"]))
    )->fetchAll());

    if (!static::$container[$arParams["TABLE_NAME"]]) {
      static::$container[$arParams["TABLE_NAME"]] = static::compileEntity($hldata)->getDataClass();
    }

    $res = static::$container[$arParams["TABLE_NAME"]]::getList(
      array(
        'select' => array('*'), 'order' => array('ID' => 'ASC'),
        'filter' => array('=UF_XML_ID' => $arParams["VALUE"]),
        "cache" => ["ttl" => 3600]
      )
    )->fetchAll();
    if (is_array($res) && !empty($res)) {
      $arResult = $res;
    }

    return $arResult;
  }
}
