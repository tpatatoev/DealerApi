<?php

namespace MTI\DealerApi\V2\Views;

use DOMDocument;
use DOMElement;
use Generator;
use MTI\DealerApi\ProductsList;

/**
 * XmlWriter
 */
class XmlWriterV2
{

  public static $totalQuantity;
  /**
   * arPropsParams
   *
   * @var array
   */
  protected $arPropsParams = [
    'type' => "PROPERTY_TYPE",
    'code' => "CODE",
    'name' => "NAME",
    'in_filter' => "SMART_FILTER",
    'multiple' => "MULTIPLE",
    'sort' => "SORT",
  ];



  /**
   * arCategoryParams
   *
   * @var array
   */
  protected $arCategoryParams = [
    'id' => "id",
    'parentID' => "parentID",
  ];


  /**
   * createParam
   *
   * @param  mixed $xml
   * @param  mixed $arParam
   * @return DOMElement
   */
  private function createParam(DOMDocument $xml, $arParam): DOMElement
  {
    $domElement = $xml->createElement("param");
    foreach ($this->arPropsParams as $xmlName => $bxName) {
      $attribute = $xml->createAttribute($xmlName);
      $attribute->value = $arParam[$bxName];
      $domElement->appendChild($attribute);
    }
    return $domElement;
  }


  /**
   * createCategory
   *
   * @param  mixed $xml
   * @param  mixed $arParam
   * @return DOMElement
   */
  private function createCategory(DOMDocument $xml, $arParam): DOMElement
  {
    $domElement = $xml->createElement("category", $arParam["name"]);
    foreach ($this->arCategoryParams as $xmlName => $bxName) {
      $attribute = $xml->createAttribute($xmlName);
      $attribute->value = $arParam[$bxName];
      $domElement->appendChild($attribute);
    }
    return $domElement;


    return $xml;
  }

  /**
   * createCateroriesList
   *
   * @param  mixed $xml
   * @param  array $arCategories
   * @param  mixed $categories
   * @return void
   */
  private function createCateroriesList(DOMDocument $xml, array $arCategories, DOMElement $categories): void
  {
    foreach ($arCategories as $arCategory) {
      if (empty($arCategory)) continue;
      $category = $this->createCategory($xml, $arCategory);
      $categories->appendChild($category);
    }
  }

  /**
   * createParamsList
   *
   * @param  mixed $xml
   * @param  mixed $arParamsList
   * @param  mixed $properties
   * @return void
   */
  private function createParamsList(DOMDocument $xml, array $arParamsList, DOMElement $properties): void
  {
    foreach ($arParamsList as $arParam) {
      if (empty($arParam)) continue;
      $param = $this->createParam($xml, $arParam);
      $properties->appendChild($param);
    }
  }


  /**
   * createProductsList
   *
   * @param  DOMDocument $xml
   * @param  Generator $arProducts
   * @param  DOMElement $products
   * @param  array $productList
   * @return int
   */
  private function createProductsList(DOMDocument $xml, Generator $arProducts, DOMElement $products, array $productList): int
  {
    $i = 0;
    if (count($productList) === 0) return $i;
    $arProductFields = array('NAME', 'CREATED_DATE', 'TIMESTAMP_X', 'PREVIEW_TEXT', "DETAIL_PICTURE", 'MORE_PHOTO', 'DETAIL_TEXT', "WEIGHT", "WIDTH", "LENGTH", "HEIGHT");
    foreach ($arProducts as $element) {
      if (empty($element)) continue;
      foreach ($productList[$element["XML_ID"]] as $productXmdId) {
        $Element = $xml->createElement('product');
        foreach ($element as $key => $val) {
          if ($key == 'XML_ID') {
            $code = $xml->createAttribute('id');
            $code->value = $productXmdId;
            $Element->appendChild($code);
          }
          if (in_array($key, $arProductFields) && $val != '') {
            $param = $xml->createElement('param', htmlspecialchars($val));
            $paramCode = $xml->createAttribute('code');
            $paramCode->value = strtolower($key);
            $param->appendChild($paramCode);
            $Element->appendChild($param);
          }
          if ($key == 'IBLOCK_SECTION_ID') {
            $code = $xml->createAttribute('cat_id');
            $code->value = $val;
            $Element->appendChild($code);
          }

          if ($key == 'PROPERTIES' && is_array($val)) {
            $arData = $val;

            foreach ($arData as $arProp) {
              if ($arProp["MULTIPLE"] != "Y") {
                $param = $xml->createElement('param', htmlspecialchars($arProp["VALUE"]));

                if ($arProp["DESCRIPTION"] != "") {
                  $param = $xml->createElement('param', $arProp["DESCRIPTION"]);
                  $paramDescription = $xml->createAttribute('value');
                  $paramDescription->value = htmlspecialchars($arProp["VALUE"]);
                }
                $paramCode = $xml->createAttribute('code');
                $paramCode->value = strtolower($arProp["CODE"]);
                $paramXML_ID = $xml->createAttribute('id');
                $paramXML_ID->value = $arProp["XML_ID"];
                $paramType = $xml->createAttribute('type');
                $paramType->value = $arProp["TYPE"];
                if (!is_null($param)) {
                  $param->appendChild($paramCode);
                  $param->appendChild($paramXML_ID);
                  $param->appendChild($paramType);
                  if ($arProp["DESCRIPTION"] != "") {
                    $param->appendChild($paramDescription);
                  }
                  $Element->appendChild($param);
                }
              } else {
                $arMultipleValue = explode(ProductsList::DIVIDER, $arProp["VALUE"]);
                $arMultipleXML_ID = explode(ProductsList::DIVIDER, $arProp["XML_ID"]);

                if ($arProp["DESCRIPTION"] != "") {
                  $arMultipleDescription = explode(ProductsList::DIVIDER, $arProp["DESCRIPTION"]);
                }

                $j = 0;
                foreach ($arMultipleValue as $multipleValue) {
                  $param = $xml->createElement('param', htmlspecialchars($multipleValue));
                  $paramCode = $xml->createAttribute('code');
                  $paramCode->value = strtolower($arProp["CODE"]);
                  $paramXML_ID = $xml->createAttribute('id');
                  $paramXML_ID->value = $arMultipleXML_ID[$j];

                  if ($arProp["DESCRIPTION"] != "") {
                    $param = $xml->createElement('param', $arMultipleDescription[$j]);
                    $paramDescription = $xml->createAttribute('value');
                    $paramDescription->value = htmlspecialchars($multipleValue);
                  }

                  $paramType = $xml->createAttribute('type');
                  $paramType->value = $arProp["TYPE"];
                  if (!is_null($param)) {
                    $param->appendChild($paramCode);
                    $param->appendChild($paramXML_ID);
                    $param->appendChild($paramType);
                    if ($arProp["DESCRIPTION"] != "") {
                      $param->appendChild($paramDescription);
                    }
                    $Element->appendChild($param);
                  }
                  $j++;
                }
              }
            }
          }
        }
      }
      if ($Element)
        $products->appendChild($Element);
      $i++;
    }
    return $i;
  }

  /**
   * createFile
   *
   * @param  Generator $arProducts
   * @param  array $productList
   * @param  array $arParamsList
   * @param  array $arCategories
   * @return DOMDocument
   */
  public function createFile(Generator $arProducts, array $productList, array $arParamsList, array $arCategories): DOMDocument
  {
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->formatOutput = true;
    $root = $xml->createElement('root');

    $categories = $xml->createElement('categories');

    $this->createCateroriesList($xml, $arCategories, $categories);

    $properties = $xml->createElement("paramslist");

    $this->createParamsList($xml, $arParamsList, $properties);

    $products = $xml->createElement('productslist');

    self::$totalQuantity = $this->createProductsList($xml, $arProducts, $products, $productList);

    $totalCount = $xml->createElement('products_count', self::$totalQuantity);
    $root->appendChild($totalCount);
    $root->appendChild($xml->createElement('memory_used', round(memory_get_usage(true) / 1024 / 1024, 2)));
    $root->appendChild($categories);
    $root->appendChild($properties);
    $root->appendChild($products);
    $xml->appendChild($root);


    return $xml;

    // $fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/xml_update/export/test.xml';
    // $fileSize = $xml->save($fileName);

    // $response = array(
    //   'responseText' => $totalCount,
    //   'size' => 'Записано: ' . $fileSize / 1024 / 1024 . ' МБайт',
    //   'memory' => 'Использовано памяти: ' . memory_get_usage(true) / 1024 / 1024,
    //   'downloadPath' => 'https://' . $_SERVER['SERVER_NAME'] . '/upload/xml_update/export/test.xml'
    // );
    // print_r($response);
  }
}
