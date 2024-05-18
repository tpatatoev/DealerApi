<?php

namespace MTI\DealerApi\V2\Controllers;

use DOMDocument;
use MTI\DealerApi\BxCatalog;
use MTI\DealerApi\BxPropertyTable;
use MTI\DealerApi\V2\Models\Product;
use MTI\DealerApi\V2\Repositories\MainRepository;
use MTI\DealerApi\V2\Views\XmlWriterV2;

class MainController
{

  public const DIVIDER = "___";
  public const TRANSPORT_DISK_FILE = "FILE";
  public const TRANSPORT_STREAM_FILE = "STREAM_FILE";
  public const TRANSPORT_STREAM_ZIP = "STREAM_ZIP";
  public const TRANSPORT_STREAM = "STREAM";
  public const DISK_FILE_LOCATION = "/upload/xml_update/export/api_goods.xml";

  // protected ProductsList $productList;
  protected XmlWriterV2 $writer;
  protected array $sortedProductsList;
  protected RequestController $request;
  protected MainRepository $repository;

  public function __construct(RequestController $request, XmlWriterV2 $writer, MainRepository $repository)
  {
    $this->request = $request;
    $this->writer = $writer;
    $this->repository = $repository;
  }

  public function getTransport(): string
  {
    if ($this->request->isFileOption()) {
      $result = static::TRANSPORT_DISK_FILE;
    } else if ($this->request->isRequestedFile()) {
      $result = static::TRANSPORT_STREAM_FILE;
    } else if ($this->request->isRequestedZip()) {
      $result = static::TRANSPORT_STREAM_ZIP;
    } else {
      $result = static::TRANSPORT_STREAM;
    }
    return $result;
  }

  public function getFileLocation()
  {
    // return $this->request->isRequestedPriceProducts() ?
    return true ?
      str_replace("api_", "test_mti_api_", static::DISK_FILE_LOCATION) :
      static::DISK_FILE_LOCATION;
  }



  public function getTotalQuantity()
  {
    return $this->writer::$totalQuantity;
  }


  function loadArray(): DOMDocument
  {
    // $this->productList = ProductsList::fromArray($arProductIds);

    $transformedList =  $this->request->getProducts()->getTransformedList();

    $arProductXmlIds = array_keys($transformedList);

    $arProperties = $this->repository->getSectionsArray($arProductXmlIds);
    // dump($arSectionProperties);
    // dump($arSectionProperties);

    // if (empty($arSectionProperties['SECTIONS'])) {
    //   return $this->loadEmpty();
    // }

    // $arProperties = BxPropertyTable::getProperiesArray($arSectionProperties);

    $arCategories = BxCatalog::getCatalogTreeList($arProperties['SECTIONS']);


    // dump($arProperties["PROPERTY_LIST"]);

    $arProducts = $this->repository->getList($arProductXmlIds, $arProperties);


    // foreach ($arProducts as $arProduct) {
    //   dump($arProduct);
    // }

    $this->writer->bindProductList($this->request->getProducts());
    // dump($this->request->getProducts());
    return $this->writer->createFile($arProducts, $arProperties["PROPERTY_LIST"], $arCategories);
    // return new DOMDocument();
  }


  private function loadSection()
  {
    $arProductXmlIds = BxCatalog::formatItemsBySection([], $_REQUEST['cat_id']);
    $this->request->resetProductList($arProductXmlIds);
    return count($arProductXmlIds) ?
      $this->loadArray() :  $this->loadEmpty();
  }


  private function loadByDate()
  {
    $arProductXmlIds = BxCatalog::formatItemsBySection([], 0);
    $this->request->resetProductList($arProductXmlIds);
    return $this->loadArray();
  }


  private function emptyGenerator()
  {
    yield [];
  }

  private function loadEmpty()
  {
    $arEmpty = $this->emptyGenerator();
    $arProperties = $this->repository->getPropertiesCollection([]);
    return $this->writer->createFile($arEmpty, $arProperties, [], []);
  }

  public static function handleRequest()
  {

    // here goes logic defining what kind of request we have got
    $request = RequestController::getInstance();
    $model = new Product;
    $writer = new XmlWriterV2($model);
    $repository = MainRepository::getInstance();
    $obContent = new static($request, $writer, $repository);
    /**test */
    $obXml =  $obContent->loadArray();
    $file = $_SERVER["DOCUMENT_ROOT"] . $obContent->getFileLocation();
    $obXml->save($file);
    // if ($request->isInvalid()) {
    //   $obXml = $obContent->loadEmpty();
    // } elseif ($_REQUEST['dateSince'] || $_REQUEST['latest'] === "Y") {
    //   $obXml = $obContent->loadByDate();
    // } else {

    //   $arProducts = $obContent->request->getProducts();
    //   $obXml = $_REQUEST['cat_id'] ? $obContent->loadSection() : $obContent->loadArray();
    // }
  }
}
