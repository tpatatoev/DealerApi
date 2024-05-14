<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Iblock\SectionPropertyTable;

/**
 * SectionPropertyRepositoryTable
 */
class SectionPropertyRepositoryTable extends SectionPropertyTable
{


  public function getSectionProperties(int $sectionId): array
  {

    $obSectionParams = new RepositoryParameters;
    $obSectionParams->addOrder(["SORT" => "ASC"]);
    $obSectionParams->addSelect([
      "*",
      "PROPERTY_NAME" => "PROPERTY.NAME",
      "PROPERTY_IBLOCK_ID" => "PROPERTY.IBLOCK_ID",
      "PROPERTY_TYPE" => "PROPERTY.PROPERTY_TYPE",
      "PROPERTY_USER_TYPE" => "PROPERTY.USER_TYPE",
      "ACTIVE" => "PROPERTY.ACTIVE",
      "MULTIPLE" => "PROPERTY.MULTIPLE",
      "PROPERTY_CODE" => "PROPERTY.CODE",
      "SORT" => "PROPERTY.SORT"
    ]);

    $obSectionParams->addReference('PROPERTY', MainRepository::getInstance()->getPropertiesRepository(), ['this.PROPERTY_ID', 'ref.ID']);

    $filter = ["PROPERTY_IBLOCK_ID" => MainRepository::IBLOCK_ID, "SECTION_ID" => [0, $sectionId], "ACTIVE" => "Y"];
    $obSectionParams->addFilter($filter);

    /**@disregard */
    $dbSectionProperties = static::getList($obSectionParams->toArray());

    $arResult = [];

    while ($arProperty = $dbSectionProperties->fetch()) {
      $arProperty['SECTION_ID'] = $arProperty['SECTION_ID'] == 0 ? $sectionId : (int) $arProperty['SECTION_ID'];
      $arProperty['PROPERTY_ID'] = (int) $arProperty['PROPERTY_ID'];
      $arProperty['MULTIPLE'] = $arProperty['MULTIPLE'] ? "true" : "false";
      $arProperty['SMART_FILTER'] = $arProperty['SMART_FILTER'] ? "true" : "false";
      $arResult[$arProperty['PROPERTY_ID']] = $arProperty;
    }

    return $arResult;
  }
}
