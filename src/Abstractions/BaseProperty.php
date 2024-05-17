<?php

namespace MTI\DealerApi\V2\Abstractions;

use MTI\DealerApi\V2\Interfaces\ISectionProperty;

/**
 * @BaseProperty
 *
 *@var protected $PROPERTY_USER_TYPE
 *@var protected $PROPERTY_TYPE;
 *@var protected $PROPERTY_ID;
 *@var protected $PROPERTY_NAME;
 *@var protected $PROPERTY_CODE;
 *@var protected $MULTIPLE;
 */
abstract class BaseProperty extends BaseEntity implements ISectionProperty
{
  protected $PROPERTY_USER_TYPE;
  protected $PROPERTY_TYPE;
  protected $PROPERTY_ID;
  protected $PROPERTY_NAME;
  protected $PROPERTY_CODE;
  protected $MULTIPLE;
  protected $SECTION_ID;

  public function getSectionId():int
  {
    return $this->SECTION_ID;
  }

  public function getPropertyId()
  {
    return $this->PROPERTY_ID;
  }

}
