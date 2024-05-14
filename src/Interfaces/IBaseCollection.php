<?php

namespace MTI\DealerApi\V2\Interfaces;

interface IBaseCollection
{
  public function add(): void;
  public function all(): array;
}
