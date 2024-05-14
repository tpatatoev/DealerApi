<?php

namespace  MTI\DealerApi\V2\Repositories;

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use MTI\ORM\FileTable;

/**
 * ProductPropertiesRepository
 */
class ProductPropertiesRepository extends ElementPropertyTable
{

  public function getPhotos($itemId): array
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "ID", "*", 'PROPERTY_ID' => "IBLOCK_PROPERTY_ID", 'PROPERTY_NAME' => 'PROPERTY.NAME', 'PROPERTY_CODE' => 'PROPERTY.CODE',
      'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE', 'PROPERTY_MULTIPLE' => 'PROPERTY.MULTIPLE', 'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE',
      'PROPERTY_USER_TYPE' => 'PROPERTY.USER_TYPE', 'PROPERTY_USER_TYPE_SETTINGS' => 'PROPERTY.USER_TYPE_SETTINGS',
      "PROPERTY_USER_TYPE_SETTINGS_LIST" => "PROPERTY.USER_TYPE_SETTINGS_LIST", 'PROPERTY_FILE_SUBDIR' => 'FILE.SUBDIR', 'PROPERTY_FILE_FILE_NAME' => 'FILE.FILE_NAME',
    ])
      ->addReference('FILE', FileTable::class, ['this.VALUE', 'ref.ID'])
      ->addReference('SECTION', PropertyTable::class, ['this.IBLOCK_PROPERTY_ID', 'ref.ID'])
      ->addFilter(["IBLOCK_ELEMENT_ID" => $itemId, "PROPERTY_CODE" => "MORE_PHOTOS"])
      ->addCache(["ttl" => 3600, "cache_joins" => true]);

    $propRes = static::getList($obParams->toArray());

    $arResult = [];
    while ($prop = $propRes->Fetch()) {
      $arResult[] = $prop;
    }
    return $arResult;
  }


  public function getProperties($itemId, array $arPropertyIds)
  {
    $obParams = new RepositoryParameters;
    $obParams->addSelect([
      "ID", "*", 'PROPERTY_ID' => "IBLOCK_PROPERTY_ID", 'PROPERTY_NAME' => 'PROPERTY.NAME', 'PROPERTY_CODE' => 'PROPERTY.CODE',
      'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE', 'PROPERTY_MULTIPLE' => 'PROPERTY.MULTIPLE', 'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE',
      'PROPERTY_USER_TYPE' => 'PROPERTY.USER_TYPE', 'PROPERTY_USER_TYPE_SETTINGS' => 'PROPERTY.USER_TYPE_SETTINGS',
      "PROPERTY_USER_TYPE_SETTINGS_LIST" => "PROPERTY.USER_TYPE_SETTINGS_LIST", 'PROPERTY_ENUM_VALUE' => 'PROPERTY_ENUMERATION.VALUE'
    ])
      ->addReference('PROPERTY', PropertyTable::class, ['this.IBLOCK_PROPERTY_ID', 'ref.ID'])
      ->addReference('PROPERTY_ENUMERATION', PropertyEnumerationTable::class, ['this.VALUE', 'ref.ID'])
      ->addFilter(["IBLOCK_ELEMENT_ID" => $itemId, "!PROPERTY_CODE" => "MORE_PHOTO", "PROPERTY_ID" => $arPropertyIds])
      ->addCache(["ttl" => 3600, "cache_joins" => true]);

    $dbProperties = static::getList($obParams->toArray());

    while ($arProperty = $dbProperties->Fetch()) {
      $arResult[$arProperty["PROPERTY_ID"]][] = $arProperty;
    }

    $arResult[0] = static::getPhotos($itemId);
    return $arResult;
  }
}
