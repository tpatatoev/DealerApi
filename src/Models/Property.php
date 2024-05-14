<?php

namespace MTI\DealerApi\V2\Models;

use MTI\DealerApi\V2\Abstractions\BaseProperty;

/**
 * @Property
 *
 *@var protected $PROPERTY_USER_TYPE
 *@var protected $PROPERTY_TYPE;
 *@var protected $PROPERTY_ID;
 *@var protected $PROPERTY_NAME;
 *@var protected $PROPERTY_CODE;
 *@var protected $MULTIPLE;
 *@var protected $SORT;
 */
class Property extends BaseProperty
{
  protected $SORT;
  public function set($name, $value): void
  {
    $this->$name = $value;
  }
}
