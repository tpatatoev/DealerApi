<?php

namespace MTI\DealerApi\V2\Views;

use DOMDocument;
use DOMElement;
use Generator;
use MTI\DealerApi\ProductsList;
use MTI\DealerApi\V2\Factories\ProductFactory;
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

  protected array $productList = [];

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

  protected DOMDocument $document;

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
  private function createParam(Property $arParam): DOMElement
  {
    $domElement = $this->document->createElement("param");
    foreach ($this->arPropsParams as $xmlName => $bxName) {
      $attribute = $this->createAttribute($xmlName,  $arParam->get($bxName));
      $domElement->appendChild($attribute);
    }
    return $domElement;
  }

  private function createAttribute($name, $value): \DOMAttr
  {
    $attribute = $this->document->createAttribute($name);
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
  private function createCategory($arParam): DOMElement
  {
    $domElement = $this->document->createElement("category", $arParam["name"]);
    foreach ($this->arCategoryParams as $xmlName => $bxName) {
      $attribute = $this->document->createAttribute($xmlName);
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
  private function createCategoriesList(array $arCategories, DOMElement $categories): void
  {
    foreach ($arCategories as $arCategory) {
      if (empty($arCategory)) continue;
      $category = $this->createCategory($arCategory);
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
  private function createParamsList(PropertyCollection $arParamsList, DOMElement $properties): void
  {
    foreach ($arParamsList->all() as $paramModel) {
      // if (empty($arParam)) continue;
      $param = $this->createParam($paramModel);
      $properties->appendChild($param);
    }
  }



  /**
   * createProduct
   *
   * @param  mixed $productXmdId
   * @param  \MTI\DealerApi\V2\Models\Product $productModel
   * @return DOMElement
   */
  protected function createProduct($productXmdId, $productModel): DOMElement
  {
    $product = $this->document->createElement('product');

    foreach (["id" => $productXmdId, "cat_id" => $productModel->get("SECTION_XML_ID")] as $attributeName => $attributeValue) {
      $code = $this->createAttribute($attributeName, $attributeValue);
      $product->appendChild($code);
    }

    foreach ($this->arProductFields as $field) {
      $param = $this->document->createElement('param', htmlspecialchars($productModel->get($field)));
      $code = $this->createAttribute("code", strtolower($field));
      $param->appendChild($code);
      $product->appendChild($param);
    }

    foreach ($productModel->getProperties() as $obProperty) {

      if (!$obProperty instanceof \MTI\DealerApi\V2\Models\PropertyValue) {
        continue;
      }
      $code = $obProperty->get("PROPERTY_CODE");
      $id = $obProperty->getXmlId();
      $type = $obProperty->get("PROPERTY_TYPE");
      $description = $obProperty->get("DESCRIPTION") ?? $obProperty->get("DESCRIPTION");

      $paramAttributeSet = [
        "code" => $code,
        "id" => $id,
        "type" => $type
      ] + ($description ? [
        "value" => $description
      ] : []);

      $param = $this->document->createElement('param', $obProperty->getValue());

      foreach ($paramAttributeSet as $attributeName => $attributeValue) {
        $paramAttribute = $this->createAttribute($attributeName, $attributeValue);
        $param->appendChild($paramAttribute);
      }
      $product->appendChild($param);
    }
    return $product;
  }


  /**
   * createProductsList
   *
   * @param  DOMDocument $xml
   * @param  Generator<\MTI\DealerApi\V2\Models\Product> $arProducts
   * @param  DOMElement $products
   * @return int
   */
  private function createProductsList(Generator $arProducts, DOMElement $products): int
  {
    $i = 0;
    if (count($this->productList) === 0) return $i;

    /**
     * @var \MTI\DealerApi\V2\Models\Product $productModel
     */
    foreach ($arProducts as $productModel) {
      $xmlId = $productModel->get("XML_ID");
      foreach ($this->productList[$xmlId] as $productXmdId) {
        $product = $this->createProduct($productXmdId, $productModel);
        if ($product instanceof DOMElement) {
          $products->appendChild($product);
          $i++;
        }
      }
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
    $this->document = new DOMDocument("1.0", "UTF-8");
    $this->document->formatOutput = true;
    $root = $this->document->createElement('root');

    $categories = $this->document->createElement('categorieslist');

    $this->createCategoriesList($arCategories, $categories);

    $properties = $this->document->createElement("paramslist");

    $this->createParamsList($arParamsList, $properties);

    $products = $this->document->createElement('productslist');

    self::$totalQuantity = $this->createProductsList($arProducts, $products);
    $totalCount = $this->document->createElement('products_count', self::$totalQuantity);
    $root->appendChild($totalCount);
    $root->appendChild($this->document->createElement('memory_used', round(memory_get_usage(true) / 1024 / 1024, 2)));
    $root->appendChild($categories);
    $root->appendChild($properties);
    $root->appendChild($products);
    $this->document->appendChild($root);
    return $this->document;
  }
}
