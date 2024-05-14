<?php

namespace MTI\DealerApi\V2\Interfaces;

interface IBaseEntity
{
  public function getType(): string;
  public function set($name, $value): void;
  public function getFields(): array;
  public function isComplexType(): bool;
}
