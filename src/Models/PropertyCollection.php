<?php

namespace MTI\DealerApi\V2\Models;

use MTI\DealerApi\V2\Abstractions\BasePropertyCollection;
use MTI\DealerApi\V2\Interfaces\IBaseProperty;

class PropertyCollection extends BasePropertyCollection
{

  public function __construct(IBaseProperty ...$property)
  {
    $this->list = $property;
  }
}
