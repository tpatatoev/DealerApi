<?php

namespace MTI\DealerApi\V2\Controllers;

use ApiRouter;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Date;
use MTI\Base\Singleton;
use MTI\DealerApi\ProductsList;

/**
 * Request Singleton
 */
class RequestController extends Singleton
{
  protected $dateSince;
  protected ProductsList $arProducts;
  protected $catId;
  protected string $key;
  protected string $browserOption;
  protected string $fileOption;
  const DATE_FORMAT = "Y-m-d";


  protected function __construct()
  {
    $this->arProducts = $this->setProductList();
    $this->catId = empty($_REQUEST['cat_id']) ? 0 : $_REQUEST['cat_id'];
    $this->dateSince = $this->setDateSince();
    $this->key = $_REQUEST['key'];
    $this->browserOption = Option::get("mti", "download_key");
    $this->fileOption = Option::get("mti", "save_to_file_key");
  }

  protected function setProductList()
  {
    $arProducts = $_REQUEST['arProducts'] ? explode(" ", $_REQUEST['arProducts']) : [];
    $arProducts = $this->isRequestedPriceProducts() ? ApiRouter::GetLatestProducts() : $arProducts;
    return ProductsList::fromArray($arProducts);
  }

  public function resetProductList(array $arProducts)
  {
    $this->arProducts = ProductsList::fromArray($arProducts);
  }

  /**
   * setIsValid
   *
   * @return bool
   */
  public function isInvalid(): bool
  {
    return (!$this->isBrowserOption() && !$this->isFileOption()) ||
      (count($this->arProducts->getList()) < 1 && !$this->catId && !$this->dateSince);
  }

  protected function setDateSince(): string
  {
    if (empty($_REQUEST['dateSince']) && empty($_REQUEST["latest"])) return "";

    if (time() < Date::createFromPhp(new \DateTime($_REQUEST["dateSince"]))->getTimestamp()) {
      $_REQUEST['dateSince'] = $value = (new Date)->format(self::DATE_FORMAT);
    } elseif ($_REQUEST["latest"] === "Y") {
      $_REQUEST['dateSince'] = $value = (new Date)->add("-1D")->format(self::DATE_FORMAT);
    } else {
      $_REQUEST['dateSince'] = $value = Date::createFromPhp(new \DateTime($_REQUEST["dateSince"]))->format(self::DATE_FORMAT);
    }
    return $value;
  }

  public function isRequestedPriceProducts()
  {
    return $_REQUEST["arProducts"] === 'latest';
  }

  public function isRequestedFile()
  {
    return $_REQUEST["asFile"] === "Y";
  }

  public function isRequestedZip()
  {
    return $_REQUEST["asZip"] === "Y";
  }

  public function isBrowserOption()
  {
    return $this->key == $this->browserOption;
  }

  public function isFileOption()
  {
    return $this->key == $this->fileOption;
  }

  public function getDateSince()
  {
    return $this->dateSince;
  }


  public function getProducts()
  {
    return $this->arProducts;
  }


  public function getCatId()
  {
    return $this->catId;
  }
}
