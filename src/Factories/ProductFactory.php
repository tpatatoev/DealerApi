<?php

namespace MTI\DealerApi\V2\Factories;

use MTI\DealerApi\V2\Models\Product;

class ProductFactory
{

  public static function fromArray(array $arProperties): Product
  {
    $product = new Product;
    $arProductFields = $product->getFields();

    foreach ($arProductFields as $field) {

      $fieldValue = $field ===  $product::PROPERTIES_FIELD ?
        PropertiesCollectionFactory::from3DArray($arProperties[$product::PROPERTIES_FIELD]) :
        $arProperties[$field];

      $product->set($field, $fieldValue);
    }
    return $product;
  }
}
