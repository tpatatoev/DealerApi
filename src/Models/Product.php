<?php

namespace MTI\DealerApi\V2\Models;

use MTI\DealerApi\V2\Abstractions\BaseProduct;

/**
 * Product
 */
class Product extends BaseProduct
{


    /**
     * getProperties
     *
     * @return array<PropertyValue>
     */
    public function getProperties()
    {
        return $this->PROPERTIES->all();
    }
}
