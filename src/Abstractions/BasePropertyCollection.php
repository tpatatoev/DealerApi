<?php

namespace MTI\DealerApi\V2\Abstractions;

use MTI\DealerApi\V2\Interfaces\IBaseProperties;



/**
 * BasePropertyCollection
 */
abstract class BasePropertyCollection implements IBaseProperties
{
  private $list = [];

  public function __construct(BaseProperty ...$property)
  {
    $this->list = $property;
  }

  public function add(): void
  {
  }

  public function all(): array
  {
    return $this->list;
  }
}
