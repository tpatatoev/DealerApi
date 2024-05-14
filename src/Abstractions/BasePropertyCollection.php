<?php

namespace MTI\DealerApi\V2\Abstractions;

use MTI\DealerApi\V2\Interfaces\IBaseCollection;
use MTI\DealerApi\V2\Interfaces\IBaseEntity;


/**
 * BasePropertyCollection
 */
abstract class BasePropertyCollection implements IBaseCollection
{
  protected array $list = [];

  public function __construct(IBaseEntity ...$property)
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
