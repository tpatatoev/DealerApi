<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Iblock\SectionPropertyTable;

/**
 * SectionPropertyRepositoryTable
 */
class SectionPropertyRepositoryTable extends SectionPropertyTable
{


  public function getSectionProperties(array $sectionIds): array
  {

    $obSectionParams = new RepositoryParameters;
    $obSectionParams->addOrder(["SORT" => "ASC"]);
    $obSectionParams->addSelect([
      // "*",
      'SECTION_ID',
      'PROPERTY_ID',
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

    $filter = ["PROPERTY_IBLOCK_ID" => MainRepository::IBLOCK_ID, "SECTION_ID" => [0, ...$sectionIds], "ACTIVE" => "Y"];
    $obSectionParams->addFilter($filter);

    /**@disregard */
    $dbSectionProperties = static::getList($obSectionParams->toArray());

    $arResult = [
      0 => [], //Property list
      1 => [], //Section Properties
    ];

    while ($arProperty = $dbSectionProperties->fetch()) {
      $arProperty['SECTION_ID'] = (int) $arProperty['SECTION_ID'];
      $arProperty['PROPERTY_ID'] = (int) $arProperty['PROPERTY_ID'];
      $arProperty['MULTIPLE'] = $arProperty['MULTIPLE'] ? "true" : "false";
      $arProperty['SMART_FILTER'] = $arProperty['SMART_FILTER'] ? "true" : "false";
      $arResult[0][$arProperty['PROPERTY_ID']] = $arProperty;

      if ($arProperty['SECTION_ID'] === 0) {
        foreach ($sectionIds as $sectionId) {
          $arResult[1][$sectionId][$arProperty['PROPERTY_ID']] = $arProperty;
        }
      } else {
        $arResult[1][$arProperty['SECTION_ID']][$arProperty['PROPERTY_ID']] = $arProperty;
      }
    }

    return $arResult;
  }
}
