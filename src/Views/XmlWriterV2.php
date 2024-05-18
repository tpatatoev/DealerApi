<?php

namespace MTI\DealerApi\V2\Views;

use DOMDocument;
use DOMElement;
use Generator;
use MTI\DealerApi\ProductsList;
use MTI\DealerApi\V2\Models\Product;
use MTI\DealerApi\V2\Models\Property;
use MTI\DealerApi\V2\Models\PropertyCollection;

/**
 * XmlWriter
 *   
 *
 * @var int static  totalQuantity
 * @var array arPropsParams
 * @var array arProductFields
 * @var array arCategoryParams
 * @var  protected Product $model
 * 
 *  
 */
class XmlWriterV2
{

  public static $totalQuantity;

  protected array $productList;

  protected $arPropsParams = [
    'type' => "PROPERTY_TYPE",
    'code' => "PROPERTY_CODE",
    'name' => "PROPERTY_NAME",
    'in_filter' => "SMART_FILTER",
    'multiple' => "MULTIPLE",
    'sort' => "SORT",
  ];

  protected $arPropsValuesParams = [
    'type' => "PROPERTY_TYPE",
    'code' => "PROPERTY_CODE",
    'value' => "DESCRIPTION",
    'id' => "VALUE_XML_ID",
  ];

  protected array $arProductFields;

  protected $arCategoryParams = [
    'id' => "id",
    'parentID' => "parentID",
  ];

  protected Product $model;


  public function __construct(Product $model)
  {
    $this->model = $model;
    $this->arProductFields = $this->model->getProductFields();
  }

  /**
   * createParam
   *
   * @param  DOMDocument $xml
   * @param  Property $arParam
   * @return DOMElement
   */
  private function createParam(DOMDocument $xml, Property $arParam): DOMElement
  {
    $domElement = $xml->createElement("param");
    foreach ($this->arPropsParams as $xmlName => $bxName) {
      $attribute = $this->createAttribute($xml, $xmlName,  $arParam->get($bxName));
      $domElement->appendChild($attribute);
    }
    return $domElement;
  }

  private function createAttribute(DOMDocument $xml, $name, $value): \DOMAttr
  {
    $attribute = $xml->createAttribute($name);
    if ($value)
      $attribute->value = $value;
    return $attribute;
  }

  /**
   * function bindProductList
   *
   * @param  \MTI\DealerApi\ProductsList $list
   * @return void
   */
  public function bindProductList(ProductsList $list)
  {
    $this->productList = $list->getTransformedList();
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
  }

  /**
   * createCateroriesList
   *
   * @param  mixed $xml
   * @param  array $arCategories
   * @param  mixed $categories
   * @return void
   */
  private function createCategoriesList(DOMDocument $xml, array $arCategories, DOMElement $categories): void
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
   * @param  DOMDocument $xml
   * @param  PropertyCollection $arParamsList
   * @param  DOMElement $properties
   * @return void
   */
  private function createParamsList(DOMDocument $xml, PropertyCollection $arParamsList, DOMElement $properties): void
  {
    foreach ($arParamsList->all() as $paramModel) {
      // if (empty($arParam)) continue;
      $param = $this->createParam($xml, $paramModel);
      $properties->appendChild($param);
    }
  }


  /**
   * createProductsList
   *
   * @param  DOMDocument $xml
   * @param  Generator<\MTI\DealerApi\V2\Models\Product> $arProducts
   * @param  DOMElement $products
   * @return int
   */
  private function createProductsList(DOMDocument $xml, Generator $arProducts, DOMElement $products): int
  {
    $i = 0;
    if (count($this->productList) === 0) return $i;

    /**
     * @var \MTI\DealerApi\V2\Models\Product $productModel
     */
    foreach ($arProducts as $productModel) {
      $xmlId = $productModel->get("XML_ID");
      $catId = $productModel->get("SECTION_XML_ID");
      foreach ($this->productList[$xmlId] as $productXmdId) {
        $Element = $xml->createElement('product');

        foreach (["id" => $productXmdId, "cat_id" => $catId] as $attributeName => $attributeValue) {
          $code = $this->createAttribute($xml, $attributeName, $attributeValue);
          $Element->appendChild($code);
        }

        foreach ($this->arProductFields as $field) {
          $param = $xml->createElement('param', htmlspecialchars($productModel->get($field)));
          $paramCode = $xml->createAttribute('code');
          $paramCode->value = strtolower($field);
          $param->appendChild($paramCode);
          $Element->appendChild($param);
        }

        foreach ($productModel->getProperties() as $obProperty) {
          $code = $obProperty->get("PROPERTY_CODE");
          $id = $obProperty->getXmlId();
          $type = $obProperty->get("PROPERTY_TYPE");
          $description = $obProperty->get("DESCRIPTION") ? $obProperty->get("DESCRIPTION") : null;

          $paramAttributeSet = [
            "code" => $code,
            "id" => $id,
            "type" => $type
          ] + ($description ? [
            "value" => $description
          ] : []);

          $param = $xml->createElement('param', $obProperty->getValue());

          foreach ($paramAttributeSet as $attributeName => $attributeValue) {
            $paramAttribute = $this->createAttribute($xml, $attributeName, $attributeValue);
            $param->appendChild($paramAttribute);
          }
          $Element->appendChild($param);
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
   * @param  Generator<\MTI\DealerApi\V2\Models\Product> $arProducts
   * @param  PropertyCollection $arParamsList
   * @param  array $arCategories
   * @return DOMDocument
   */
  public function createFile(Generator $arProducts, PropertyCollection $arParamsList, array $arCategories): DOMDocument
  {
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->formatOutput = true;
    $root = $xml->createElement('root');

    $categories = $xml->createElement('categories');

    $this->createCategoriesList($xml, $arCategories, $categories);

    $properties = $xml->createElement("paramslist");

    $this->createParamsList($xml, $arParamsList, $properties);

    $products = $xml->createElement('productslist');

    self::$totalQuantity = $this->createProductsList($xml, $arProducts, $products);
    $totalCount = $xml->createElement('products_count', self::$totalQuantity);
    $root->appendChild($totalCount);
    $root->appendChild($xml->createElement('memory_used', round(memory_get_usage(true) / 1024 / 1024, 2)));
    $root->appendChild($categories);
    $root->appendChild($properties);
    $root->appendChild($products);
    $xml->appendChild($root);
    return $xml;
  }
}
