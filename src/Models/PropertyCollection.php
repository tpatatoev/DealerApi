<?php

namespace MTI\DealerApi\V2\Models;

use MTI\DealerApi\V2\Abstractions\BaseProperty;
use MTI\DealerApi\V2\Abstractions\BasePropertyCollection;
use MTI\DealerApi\V2\Interfaces\IBaseEntity;

class PropertyCollection extends BasePropertyCollection
{

  public function __construct(IBaseEntity ...$property)
  {
    $this->list = $property;
  }

  public function getBySectionId(int $sectionId)
  {
    if (!$this->list[0] instanceof BaseProperty)
      return [];

    return array_map(
      fn (BaseProperty $arProperty) => $arProperty->getPropertyId(),
      array_filter(
        $this->list,
        fn (BaseProperty $arProperty) => $arProperty->getSectionId() === $sectionId || 0
      )
    );
  }
}
