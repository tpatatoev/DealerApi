<?php

namespace  MTI\DealerApi\V2\Repositories;

use Bitrix\Main\ORM\Fields\ExpressionField;
use CFile;
use CIBlockSectionPropertyLink;
use Generator;

class ProductRepositoryTable extends ElementDistriTable
{

  const XML_ID_FIELD = "XML_ID";
  const ID_FIELD = "ID";
  const NAME_FIELD = "NAME";


  public static function getFilePath(int $fileId)
  {
    return 'https://' . $_SERVER["SERVER_NAME"] . CFile::GetPath($fileId);
  }


  private static function normalizePropertiesArray(array $arProperties)
  {
    // echo json_encode($arProperties, JSON_PRETTY_PRINT);
    foreach ($arProperties as &$property) {
      $property["SMART_FILTER"] = $property["SMART_FILTER"] === "Y" ? "true" : "false";
      $property["MULTIPLE"] = $property["MULTIPLE"] === "Y" ? "true" : "false";
      // $property["INHERITED_FROM"] = static::getSection($property["INHERITED_FROM"])["XML_ID"];
      if ($property["PROPERTY_TYPE"] == 'S' && $property["USER_TYPE"] == 'directory') {
        $property["PROPERTY_TYPE"] = 'L';
      }
    }
    unset($property);
    return $arProperties;
  }



  /**
   * getElements
   *
   * @param  mixed $arProductXmlIds
   * @return Generator
   */
  public static function getElements(array $arProductXmlIds, array $arPropertyIds): Generator
  {

    $order = ['XML_ID' => 'ASC'];

    $filter = [
      'ACTIVE' => 'Y',
      'IBLOCK_ID' => MainRepository::IBLOCK_ID,
      "XML_ID" => $arProductXmlIds
    ];

    // if ($_REQUEST['cat_id']) {
    //   $filter["IBLOCK_SECTION_ID"] = static::getSection($_REQUEST["cat_id"], "XML")["ID"];
    // }

    if ($_REQUEST["cat_id"] && count($arProductXmlIds) === 1) {
      unset($filter["XML_ID"]);
    }

    $arSelect = array(
      'ID', 'IBLOCK_ID', 'XML_ID', 'NAME', 'IBLOCK_SECTION_ID', 'DETAIL_PICTURE',
      'PREVIEW_TEXT', 'DETAIL_TEXT', 'CREATED_DATE', 'TIMESTAMP_X', "WEIGHT", "WIDTH", "LENGTH", "HEIGHT",
    );

    yield [];
  }
}
