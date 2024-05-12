<?

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Iblock\PropertyTable;

/**
 * PropertiesRepository
 */
class PropertiesRepository extends PropertyTable
{

  public static function flattenArray(array $array)
  {
    $result = [];
    foreach (array_keys($array) as $key) {
      foreach ($array[$key] as $key) {
        $result[] = $key["PROPERTY_ID"];
      }
    }
    return array_unique($result);
  }


  public static function getProperty(int $propertyId, array $arSectionProperties)
  {

    $inFilter = "false";
    foreach ($arSectionProperties as  $arProperties) {
      foreach ($arProperties as  $arProperty) {
        if ((int) $arProperty["PROPERTY_ID"] === $propertyId) {
          $inFilter = $arProperty["SMART_FILTER"];
        }
      }
    }
    return $inFilter;
  }

  public static function getBySections(array $arSectionProperties)
  {
    $defaultPropertiesCodes = ['guarantee', 'partno', 'model', 'country', 'tnved', 'barcode'];

    $filter = [
      "LOGIC" => "OR",
      [
        'IBLOCK_ID' => ProductRepository::IBLOCK_ID,
        'ACTIVE' => 'Y',
        'CODE' => $defaultPropertiesCodes
      ],
      [
        'IBLOCK_ID' => ProductRepository::IBLOCK_ID,
        'ACTIVE' => 'Y',
        'ID' =>  static::flattenArray($arSectionProperties)
      ]
    ];

    $obParams = new RepositoryParameters;
    $obParams->addFilter($filter);
    $obParams->addSelect(['ID', 'NAME', 'CODE', 'SORT', 'PROPERTY_TYPE', 'USER_TYPE', 'MULTIPLE']);
    $dbProperty = static::getList($obParams->toArray());

    // echo json_encode($arSectionProperties, JSON_PRETTY_PRINT);
    $arResult = [];
    while ($arProperty = $dbProperty->fetch()) {
      $arResult[$arProperty["ID"]]["SMART_FILTER"] = static::getProperty($arProperty["ID"], $arSectionProperties);
      $arResult[$arProperty["ID"]]["NAME"] = $arProperty["NAME"];
      $arResult[$arProperty["ID"]]["CODE"] = ToLower($arProperty["CODE"]);
      $arResult[$arProperty["ID"]]["MULTIPLE"] = $arProperty["MULTIPLE"] == "Y" ?  "true" : "false";
      $arResult[$arProperty["ID"]]["SORT"] = empty($arSectionProp[$arProperty["ID"]]["SORT"]) ? $arProperty["SORT"] : $arSectionProp[$arProperty["ID"]]["SORT"];
      // $arResult[$arProperty["ID"]]["PROPERTY_TYPE"] = $arProperty["USER_TYPE"] == "HTML" ? $arProperty["PROPERTY_TYPE"] . ":" . $arProperty["USER_TYPE"] : $arProperty["PROPERTY_TYPE"];
    }
    return $arResult;
  }
}
