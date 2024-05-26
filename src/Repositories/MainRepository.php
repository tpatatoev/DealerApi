<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Generator;
use Bitrix\Iblock\ElementTable;
use MTI\Base\Singleton;
use MTI\DealerApi\V2\Factories\ProductFactory;
use MTI\DealerApi\V2\Factories\PropertiesCollectionFactory;
use MTI\ORM\FileTable;
use Bitrix\Iblock\Elements\ElementDistriTable;

class MainRepository extends Singleton
{

  const IBLOCK_ID = 31;
  const XML_ID_FIELD = "XML_ID";
  const ID_FIELD = "ID";
  const NAME_FIELD = "NAME";
  const IBLOCK_SECTION_ID = "IBLOCK_SECTION_ID";

  protected function __construct()
  {
  }


  public function getProductRepository()
  {
    return ElementTable::class;
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

  public function getSectionsArray(array $arProductXmlIds, $sectionXmlId = null): array
  {

    $arResult = [
      "SECTIONS" => [],
      "PROPERTY_LIST" => [],
      "SECTION_PROPERTIES" => [],
    ];

    if ($sectionXmlId) {
      $arResult["SECTIONS"][] = $this->getSection($sectionXmlId, static::XML_ID_FIELD)[static::ID_FIELD];
    } else {

      $obParams = new RepositoryParameters;
      $obParams->addSelect(['SECTION_ID'])
        ->addFilter(["XML_ID" => $arProductXmlIds, "IBLOCK_ID" => static::IBLOCK_ID])
        ->addLimit(count($arProductXmlIds))
        ->addExpression('SECTION_ID', 'DISTINCT %s', ['IBLOCK_SECTION_ID'])
        ->addCache(["ttl" => 3600 * 24,]);

      $iterator = ($this->getProductRepository())::getList($obParams->toArray());
      while ($arItem = $iterator->fetch()) {
        $arResult["SECTIONS"][] = $arItem['SECTION_ID'];
      }
    }

    [$arResult["PROPERTY_LIST"], $arResult["SECTION_PROPERTIES"]] = ($this->getSectionPropertiesRepository())::getSectionProperties($arResult['SECTIONS']);
    $arResult["PROPERTY_LIST"] = $this->getPropertiesCollection($arResult["PROPERTY_LIST"]);
    return $arResult;
  }



  /**
   * getList
   *
   * @param  array $arProductXmlIds
   * @param  array $arSectionProperties
   * @return Generator<\MTI\DealerApi\V2\Models\Product>
   */
  public function getList(array $arProductXmlIds, array $arSectionProperties): Generator
  {
    $obParams = new RepositoryParameters;
    $obParams->addReference('FILE', FileTable::class, ['this.DETAIL_PICTURE', 'ref.ID'])
      ->addExpression('DETAIL_PICTURE_FILE', "CONCAT ('https://" . $_SERVER['SERVER_NAME'] . "/upload/', %s, '/', %s)", ['FILE.SUBDIR', "FILE.FILE_NAME"])
      ->addReference('CATALOG', ProductTable::class, ['this.ID', 'ref.ID'])
      ->addCache(["ttl" => 3600 * 2]);

    $filter = [
      "=WF_STATUS_ID" => 1,
      'ACTIVE' => 'Y',
      "IBLOCK_ID" => static::IBLOCK_ID,
      static::IBLOCK_SECTION_ID => $arSectionProperties['SECTIONS'],
      static::XML_ID_FIELD => $arProductXmlIds
    ];

    $select = [
      "ID", "XML_ID", "NAME", "DETAIL_TEXT" => "DETAIL_TEXT", "PREVIEW_TEXT", "IBLOCK_SECTION_ID", "TIMESTAMP_X", "DATE_CREATE",
      "DETAIL_PICTURE_FILE", "SECTION_XML_ID" => "IBLOCK_SECTION.XML_ID", "DETAIL_PICTURE_DIR" => "FILE.SUBDIR", "DETAIL_PICTURE_FILE_NAME" => "FILE.FILE_NAME",
      "WIDTH" => "CATALOG.WIDTH", "LENGTH" => "CATALOG.LENGTH", "HEIGHT" => "CATALOG.HEIGHT", "WEIGHT" => "CATALOG.WEIGHT",
    ];

    if ($_REQUEST["cat_id"] && count($arProductXmlIds) === 1) {
      unset($filter[static::XML_ID_FIELD], $select["DETAIL_TEXT"]);
    }



    $obParams->addFilter($filter)
      ->addSelect($select);


    // file_put_contents(MTI_LS_FOLDER . "1testfile.txt", json_encode($obParams->toArray(), JSON_PRETTY_PRINT));

    $dbElements = ($this->getProductRepository())::getList($obParams->toArray());

    $currentSectionProperties = [];

    while ($arItem = $dbElements->fetch()) {

      if (empty($currentSectionProperties[$arItem[static::IBLOCK_SECTION_ID]])) {
        $currentSectionProperties[$arItem[static::IBLOCK_SECTION_ID]] =
          array_keys($arSectionProperties["SECTION_PROPERTIES"][$arItem[static::IBLOCK_SECTION_ID]]);
      }

      $arItem['DETAIL_TEXT'] = str_replace(array("\r", "\n"), '<br/>', $arItem['DETAIL_TEXT']);
      $arItem['DETAIL_PICTURE'] = $arItem["DETAIL_PICTURE_FILE"];

      $arPropertySet = [];

      foreach ($arSectionProperties["SECTION_PROPERTIES"][$arItem[static::IBLOCK_SECTION_ID]] as $key => $arProperty) {
        $arProperty["VALUE"] = $arProperty["VALUE_XML_ID"] =  $arProperty["VALUE_ENUM"] = null;
        $arPropertySet[$key][] = $arProperty;
      }

      $arItem["PROPERTIES"] = array_replace(
        $arPropertySet,
        $this->getProperties($arItem["ID"], $currentSectionProperties[$arItem[static::IBLOCK_SECTION_ID]])
      );

      yield ProductFactory::fromArray($arItem);
    }
  }




  protected function getProperties($itemId, array $arPropertyIds)
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "VALUE_XML_ID" => "VALUE_ENUM", 'IBLOCK_PROPERTY_ID', "VALUE", 'PROPERTY_ID' => "IBLOCK_PROPERTY_ID", 'PROPERTY_NAME' => 'PROPERTY.NAME', 'PROPERTY_CODE' => 'PROPERTY.CODE',
      'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE', 'MULTIPLE' => 'PROPERTY.MULTIPLE',
      'PROPERTY_USER_TYPE' => 'PROPERTY.USER_TYPE', 'PROPERTY_USER_TYPE_SETTINGS' => 'PROPERTY.USER_TYPE_SETTINGS',
      "PROPERTY_USER_TYPE_SETTINGS_LIST" => "PROPERTY.USER_TYPE_SETTINGS_LIST", 'PROPERTY_ENUM_VALUE' => 'PROPERTY_ENUMERATION.VALUE'
    ])
      ->addReference('PROPERTY', $this->getPropertiesRepository(), ['this.IBLOCK_PROPERTY_ID', 'ref.ID'])
      ->addReference('PROPERTY_ENUMERATION', PropertyEnumerationTable::class, ['this.VALUE', 'ref.ID'])
      ->addFilter(["IBLOCK_ELEMENT_ID" => $itemId, "!PROPERTY_CODE" => ["MORE_PHOTO", "file"], "PROPERTY_ID" => $arPropertyIds]);


    $dbProperties = ($this->getProductProperties())::getList($obParams->toArray());

    while ($arProperty = $dbProperties->fetch()) {

      if ($arProperty['PROPERTY_USER_TYPE'] === 'directory') {

        $arValue = HighloadRepositoryTable::getValue([
          "TABLE_NAME" => $arProperty["PROPERTY_USER_TYPE_SETTINGS_LIST"]["TABLE_NAME"],
          "VALUE" => $arProperty["VALUE"]
        ])[0];

        $arProperty = array_merge($arProperty, $arValue);
      }

      if (empty($arProperty)) continue;
      $arProperty['PROPERTY_CODE'] = ToLower($arProperty['PROPERTY_CODE']);
      $arResult[$arProperty["PROPERTY_ID"]][] = $arProperty;
    }

    $arFiles = $this->getPhotos($itemId);
    foreach ($arFiles as $key => $arFile) {
      $arResult[$key] = $arFile;
    }

    return $arResult;
  }


  protected function getPhotos($itemId): array
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "IBLOCK_PROPERTY_ID", "FILE_VALUE", "VALUE_XML_ID" => "VALUE", 'PROPERTY_ID' => "PROPERTY.ID", 'PROPERTY_NAME' => 'PROPERTY.NAME', 'PROPERTY_CODE' => 'PROPERTY.CODE',
      'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE', 'MULTIPLE' => 'PROPERTY.MULTIPLE', 'PROPERTY_USER_TYPE' => 'PROPERTY.USER_TYPE',
    ])
      ->addReference('FILE', FileTable::class, ['this.VALUE', 'ref.ID'])
      ->addReference('PROPERTY', PropertyTable::class, ['this.IBLOCK_PROPERTY_ID', 'ref.ID'])
      ->addExpression('FILE_VALUE', "CONCAT ('https://" . $_SERVER['SERVER_NAME'] . "/upload/', %s, '/', %s)", ['FILE.SUBDIR', "FILE.FILE_NAME"])
      ->addFilter(["IBLOCK_ELEMENT_ID" => $itemId, "PROPERTY.CODE" => ["MORE_PHOTO", "file"]]);


    $dbProperty = ($this->getProductProperties())::getList($obParams->toArray());

    $arResult = [];
    while ($arProperty = $dbProperty->Fetch()) {
      $arProperty['PROPERTY_CODE'] = ToLower($arProperty['PROPERTY_CODE']);
      $arProperty["VALUE"] = $arProperty["FILE_VALUE"];
      $arResult[$arProperty["IBLOCK_PROPERTY_ID"]][] = $arProperty;
    }
    return $arResult;
  }


  /**
   * formatItemsBySection
   *
   * @param  array $arProductXmlIds
   * @param  null | string $sectionXmlId
   * @param  null | string $dateSince
   * @return array
   */
  function getRequestedItemIds(array $arProductXmlIds = [], $sectionXmlId = null, $dateSince = null): array
  {

    $filter = [
      "IBLOCK_ID" => static::IBLOCK_ID,
      "ACTIVE" => "Y",
      "=WF_STATUS_ID" => 1,
    ];
    if ($sectionXmlId) {
      $filter[static::IBLOCK_SECTION_ID] = $this->getSection($sectionXmlId, static::XML_ID_FIELD)[static::ID_FIELD];
    }

    if (!empty($arProductXmlIds)) {
      $filter[static::XML_ID_FIELD] = $arProductXmlIds;
    }


    $select = [static::XML_ID_FIELD, static::IBLOCK_SECTION_ID];

    $obParams = new RepositoryParameters;


    if (empty($arProductXmlIds) &&  $sectionXmlId === 0 && $dateSince) {
      $select = array_merge($select, ['PROP_CODE' => 'PROPERTY.CODE', 'PROP_VALUE' => 'ELEMENT_PROPERTY.VALUE']);
      $filter = array_merge($filter, ["PROP_CODE" => "CONTENT_TIMESTAMP_X", ">=PROP_VALUE" => $dateSince]);

      $obParams->addReference('ELEMENT_PROPERTY', ElementPropertyTable::class, ['this.ID', 'ref.IBLOCK_ELEMENT_ID'])
        ->addReference('PROPERTY', PropertyTable::class, ['this.ELEMENT_PROPERTY.IBLOCK_PROPERTY_ID', 'ref.ID']);
    }

    $obParams->addSelect($select)
      ->addCache(["ttl" => 3600 * 24])
      ->addFilter($filter);


    $iterator = ($this->getProductRepository())::getList($obParams->toArray());
    $arResult = [];
    while ($arItem = $iterator->fetch()) {
      $arResult[] = $arItem['XML_ID'];
    }

    return $arResult;
  }

  /**
   * getSection
   *
   * @param  mixed $id
   * @param  string $fieldName
   * @return array $result
   */
  public function getSection($id, string $fieldName = 'ID'): array
  {
    $result = [];
    if (!$id) {
      return $result;
    }

    if (!in_array($fieldName, [static::XML_ID_FIELD, static::ID_FIELD, static::NAME_FIELD]))
      $fieldName = static::XML_ID_FIELD;

    $parameters = [
      'select' => [static::ID_FIELD, static::XML_ID_FIELD, 'IBLOCK_ID', static::NAME_FIELD],
      'filter' => ['IBLOCK_ID' => static::IBLOCK_ID, "ACTIVE" => "Y", $fieldName => $id],
      'order' => ['ID' => 'ASC'],
      "cache" => ["ttl" => 3600 * 24],
    ];

    $dbSections = SectionTable::getList($parameters);
    if ($section = $dbSections->fetch()) {
      $result = $section;
    }
    return $result;
  }


  /**
   * getTreeList
   *
   * @param  array $sectionIds
   * @return array
   */
  public function getTreeList(array $sectionIds = []): array
  {
    $arResult = [];
    $filter = ['IBLOCK_ID' => static::IBLOCK_ID,  "ACTIVE" => "Y"];
    if ($sectionIds)
      $filter["ID"] = $sectionIds;

    $obParams = new RepositoryParameters;
    $obParams->addSelect([static::IBLOCK_SECTION_ID, static::XML_ID_FIELD, static::NAME_FIELD, static::ID_FIELD])
      ->addFilter($filter)
      ->addCache(["ttl" => 3600 * 12]);
    $dbIterator = SectionTable::getList($obParams->toArray());

    while ($section = $dbIterator->fetch()) {
      $parentId = $section[static::IBLOCK_SECTION_ID] ?  $this->getSection($section[static::IBLOCK_SECTION_ID])[static::XML_ID_FIELD] : 0;
      if ($sectionIds)
        $arResult[$parentId] = array("name" => $this->getSection($section[static::IBLOCK_SECTION_ID])[static::NAME_FIELD], "id" => $parentId, 'parentID' => 0);
      $arResult[$section[static::XML_ID_FIELD]] = array("name" => $section[static::NAME_FIELD], "id" => $section[static::XML_ID_FIELD], 'parentID' => $parentId);
    }
    return $arResult;
  }
}
