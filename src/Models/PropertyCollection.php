<?php

namespace MTI\DealerApi\V2\Models;

use MTI\DealerApi\V2\Abstractions\BasePropertyCollection;

class PropertyCollection extends BasePropertyCollection
{
  private $list = [];

  public function __construct(Property ...$property)
  {
    $this->list = $property;
  }
}
