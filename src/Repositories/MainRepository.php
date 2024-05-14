<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Generator;
use MTI\Base\Singleton;
use MTI\DealerApi\V2\Factories\ProductFactory;
use MTI\ORM\FileTable;

class MainRepository extends Singleton
{

  const IBLOCK_ID = 31;

  protected function __construct()
  {
  }


  public function getProductRepository()
  {
    return ProductRepositoryTable::class;
  }

  public function getProductProperties()
  {
    return ProductPropertiesRepository::class;
  }

  public function getPropertiesRepository()
  {
    return PropertiesRepositoryTable::class;
  }

  public function getSectionPropertiesRepository()
  {
    return SectionPropertyRepositoryTable::class;
  }

  public function getHighloadRepository()
  {
    return HighLoadRepository::class;
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
      "SECTION_PROPERTIES" => [],
      "PROPERTIES" => [],
    ];

    while ($arItem = $iterator->fetch()) {
      if ($arResult["SECTION_PROPERTIES"][$arItem['SECTION_ID']]) continue;

      $sectionProperties = ($this->getSectionPropertiesRepository())::getSectionProperties($arItem['SECTION_ID']);

      $arResult["SECTION_PROPERTIES"][$arItem['SECTION_ID']] = $sectionProperties;
      $arResult["SECTIONS"][] = (int) $arItem['SECTION_ID'];

      $arResult["PROPERTIES"] = [
        ...array_map(
          fn ($arProperty) => $arProperty["PROPERTY_ID"],
          $sectionProperties
        ),
        ...$arResult["PROPERTIES"]
      ];
    }
    $arResult["PROPERTIES"] = array_unique($arResult["PROPERTIES"]);
    return $arResult;
  }



  /**
   * getList
   *
   * @param  array $arProductXmlIds
   * @return Generator
   */
  public function getList(array $arProductXmlIds, array $arSectionsIds): Generator
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "ID", "*", "SECTION_XML_ID" => "SECTION.XML_ID", "DETAIL_PICTURE_DIR" => "FILE.SUBDIR", "DETAIL_PICTURE_FILE_NAME" => "FILE.FILE_NAME",
      "WIDTH" => "CATALOG.WIDTH", "LENGTH" => "CATALOG.LENGTH", "HEIGHT" => "CATALOG.HEIGHT", "WEIGHT" => "CATALOG.WEIGHT",
    ]);

    $obParams->addReference('FILE', FileTable::class, ['this.DETAIL_PICTURE', 'ref.ID']);
    $obParams->addReference('SECTION', SectionTable::class, ['this.IBLOCK_SECTION_ID', 'ref.ID']);
    $obParams->addReference('CATALOG', ProductTable::class, ['this.ID', 'ref.ID']);

    $filter = [
      'ACTIVE' => 'Y',
      "IBLOCK_SECTION_ID" => $arSectionsIds,
      "XML_ID" => $arProductXmlIds
    ];

    if ($_REQUEST["cat_id"] && count($arProductXmlIds) === 1) {
      unset($filter["XML_ID"]);
    }

    $obParams->addFilter($filter);

    $res = ($this->getProductRepository())::getList($obParams->toArray());
    while ($arItem = $res->fetch()) {
      $arItem['DETAIL_TEXT'] = str_replace(array("\r", "\n"), '', $arItem['DETAIL_TEXT']);
      $arItem["PROPERTIES"] = getProperties($arItem["ID"], $arItem["IBLOCK_SECTION_ID"]);
      $p = ProductFactory::fromArray($arItem);
      yield $p;
      // echo "<pre>" . print_r($p, true) . "</pre>";

    }
  }
}
