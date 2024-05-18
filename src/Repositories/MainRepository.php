<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Generator;
use MTI\Base\Singleton;
use MTI\DealerApi\BxHighLoadTable;
use MTI\DealerApi\V2\Factories\ProductFactory;
use MTI\DealerApi\V2\Factories\PropertiesCollectionFactory;
use MTI\DealerApi\V2\Factories\PropertiesFactory;
use MTI\ORM\FileTable;

class MainRepository extends Singleton
{

  const IBLOCK_ID = 31;

  protected function __construct()
  {
  }


  public function getProductRepository()
  {
    return ElementDistriTable::class;
  }

  public function getProductProperties()
  {
    return ElementPropertyTable::class;
  }

  public function getPropertiesRepository()
  {
    return PropertyTable::class;
  }

  public function getSectionPropertiesRepository()
  {
    return SectionPropertyRepositoryTable::class;
  }

  public function getHighloadRepository()
  {
    return HighloadRepositoryTable::class;
  }

  public function getPropertiesCollection(array $array)
  {
    return PropertiesCollectionFactory::fromArray($array);
  }

  public function getSectionsArray(array $arProductXmlIds): array
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect(['SECTION_ID']);
    $obParams->addFilter(["XML_ID" => $arProductXmlIds]);
    $obParams->addExpression('SECTION_ID', 'DISTINCT %s', ['IBLOCK_SECTION_ID']);

    $iterator = ($this->getProductRepository())::getList($obParams->toArray());

    $arResult = [
      "SECTIONS" => [],
      "PROPERTY_LIST" => [],
      "SECTION_PROPERTIES" => [],
    ];

    while ($arItem = $iterator->fetch()) {
      $arResult["SECTIONS"][] = $arItem['SECTION_ID'];
    }

    [$arResult["PROPERTY_LIST"], $arResult["SECTION_PROPERTIES"]] = ($this->getSectionPropertiesRepository())::getSectionProperties($arResult['SECTIONS']);
    $arResult["PROPERTY_LIST"] = $this->getPropertiesCollection($arResult["PROPERTY_LIST"]);
    return $arResult;
  }



  /**
   * getList
   *
   * @param  array $arProductXmlIds
   * @return Generator<\MTI\DealerApi\V2\Models\Product>
   */
  public function getList(array $arProductXmlIds, array $arSectionProperties): Generator
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "ID", "*", "DETAIL_PICTURE_FILE", "SECTION_XML_ID" => "IBLOCK_SECTION.XML_ID", "DETAIL_PICTURE_DIR" => "FILE.SUBDIR", "DETAIL_PICTURE_FILE_NAME" => "FILE.FILE_NAME",
      "WIDTH" => "CATALOG.WIDTH", "LENGTH" => "CATALOG.LENGTH", "HEIGHT" => "CATALOG.HEIGHT", "WEIGHT" => "CATALOG.WEIGHT",
    ]);

    $obParams->addReference('FILE', FileTable::class, ['this.DETAIL_PICTURE', 'ref.ID']);
    $obParams->addExpression('DETAIL_PICTURE_FILE', "CONCAT ('https://" . $_SERVER['SERVER_NAME'] . "/upload/', %s, '/', %s)", ['FILE.SUBDIR', "FILE.FILE_NAME"]);
    $obParams->addReference('CATALOG', ProductTable::class, ['this.ID', 'ref.ID']);

    $filter = [
      "=WF_STATUS_ID" => 1,
      'ACTIVE' => 'Y',
      "IBLOCK_SECTION_ID" => $arSectionProperties['SECTIONS'],
      "XML_ID" => $arProductXmlIds
    ];

    if ($_REQUEST["cat_id"] && count($arProductXmlIds) === 1) {
      unset($filter["XML_ID"]);
    }

    $obParams->addFilter($filter);

    $dbElements = ($this->getProductRepository())::getList($obParams->toArray());
    $currentSectionProperties = [];
    while ($arItem = $dbElements->fetch()) {

      if (empty($currentSectionProperties[$arItem["IBLOCK_SECTION_ID"]])) {
        $currentSectionProperties[$arItem["IBLOCK_SECTION_ID"]] =
          array_keys($arSectionProperties["SECTION_PROPERTIES"][$arItem["IBLOCK_SECTION_ID"]]);
      }

      $arItem['DETAIL_TEXT'] = str_replace(array("\r", "\n"), '<br/>', $arItem['DETAIL_TEXT']);

      $arExistingValues = $this->getProperties($arItem["ID"], $currentSectionProperties[$arItem["IBLOCK_SECTION_ID"]]);
      $arPropertySet = [];

      foreach ($arSectionProperties["SECTION_PROPERTIES"][$arItem["IBLOCK_SECTION_ID"]] as $key => $arProperty) {
        $arProperty["VALUE"] = $arProperty["VALUE_XML_ID"] =  $arProperty["VALUE_ENUM"] = null;
        $arPropertySet[$key][] = $arProperty;
      }

      $arItem["PROPERTIES"] = array_replace($arPropertySet, $arExistingValues);

      // dump($arItem);
      $p = ProductFactory::fromArray($arItem);
      // $p = [];
      yield $p;
      // echo "<pre>" . print_r($p, true) . "</pre>";

    }
  }

  protected function getProperties($itemId, array $arPropertyIds)
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "VALUE_XML_ID" => "VALUE_ENUM", 'IBLOCK_PROPERTY_ID', "VALUE", 'PROPERTY_ID' => "IBLOCK_PROPERTY_ID", 'PROPERTY_NAME' => 'PROPERTY.NAME', 'PROPERTY_CODE' => 'PROPERTY.CODE',
      'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE', 'MULTIPLE' => 'PROPERTY.MULTIPLE', 'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE',
      'PROPERTY_USER_TYPE' => 'PROPERTY.USER_TYPE', 'PROPERTY_USER_TYPE_SETTINGS' => 'PROPERTY.USER_TYPE_SETTINGS',
      "PROPERTY_USER_TYPE_SETTINGS_LIST" => "PROPERTY.USER_TYPE_SETTINGS_LIST", 'PROPERTY_ENUM_VALUE' => 'PROPERTY_ENUMERATION.VALUE'
    ])
      ->addReference('PROPERTY', $this->getPropertiesRepository(), ['this.IBLOCK_PROPERTY_ID', 'ref.ID'])
      ->addReference('PROPERTY_ENUMERATION', PropertyEnumerationTable::class, ['this.VALUE', 'ref.ID'])
      ->addFilter(["IBLOCK_ELEMENT_ID" => $itemId, "!PROPERTY_CODE" => "MORE_PHOTO", "PROPERTY_ID" => $arPropertyIds])
      ->addCache(["ttl" => 3600, "cache_joins" => true]);

    $dbProperties = ($this->getProductProperties())::getList($obParams->toArray());

    while ($arProperty = $dbProperties->Fetch()) {

      if ($arProperty['PROPERTY_USER_TYPE'] === 'directory') {

        $arValue = HighloadRepositoryTable::getValue([
          "TABLE_NAME" => $arProperty["PROPERTY_USER_TYPE_SETTINGS_LIST"]["TABLE_NAME"],
          "VALUE" => $arProperty["VALUE"]
        ])[0];

        $arProperty = array_merge($arProperty, $arValue);
      }

      $arProperty['PROPERTY_CODE'] = ToLower($arProperty['PROPERTY_CODE']);
      $arResult[$arProperty["PROPERTY_ID"]][] = $arProperty;
    }
    $arPhotos = $this->getPhotos($itemId);
    $morePhotosId = $arPhotos[0]["PROPERTY_ID"] ? $arPhotos[0]["PROPERTY_ID"] : 10000;
    $arResult[$morePhotosId] = $arPhotos;
    return $arResult;
  }


  protected function getPhotos($itemId): array
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "IBLOCK_PROPERTY_ID", "FILE_VALUE", "VALUE_XML_ID" => "VALUE", 'PROPERTY_ID' => "PROPERTY.ID", 'PROPERTY_NAME' => 'PROPERTY.NAME', 'PROPERTY_CODE' => 'PROPERTY.CODE',
      'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE', 'MULTIPLE' => 'PROPERTY.MULTIPLE', 'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE',
      'PROPERTY_USER_TYPE' => 'PROPERTY.USER_TYPE',

    ])
      ->addReference('FILE', FileTable::class, ['this.VALUE', 'ref.ID'])
      ->addReference('PROPERTY', PropertyTable::class, ['this.IBLOCK_PROPERTY_ID', 'ref.ID'])
      ->addExpression('FILE_VALUE', "CONCAT ('https://" . $_SERVER['SERVER_NAME'] . "/upload/', %s, '/', %s)", ['FILE.SUBDIR', "FILE.FILE_NAME"])
      ->addFilter(["IBLOCK_ELEMENT_ID" => $itemId, "PROPERTY.CODE" => "MORE_PHOTO"])
      ->addCache(["ttl" => 3600, "cache_joins" => true]);

    $dbProperty = ($this->getProductProperties())::getList($obParams->toArray());

    $arResult = [];
    while ($arProperty = $dbProperty->Fetch()) {
      $arProperty['PROPERTY_CODE'] = ToLower($arProperty['PROPERTY_CODE']);
      $arProperty["VALUE"] = $arProperty["FILE_VALUE"];
      $arResult[] = $arProperty;
    }
    return $arResult;
  }


  private function newQuery()
  {
    /**@disregard */
    $q = new \Bitrix\Main\Entity\Query(ElementDistriTable::getEntity());
    $q->setSelect(array('ISBN', 'TITLE', 'PUBLISH_DATE'));
    $q->setFilter(array('=ID' => 1));
    $result = $q->exec();
  }
}
