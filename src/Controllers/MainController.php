<?php

namespace MTI\DealerApi\V2\Controllers;

use DOMDocument;
use Generator;
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
  protected XmlResponseController $response;

  public function __construct(RequestController $request, XmlWriterV2 $writer, MainRepository $repository, XmlResponseController $response)
  {
    $this->request = $request;
    $this->writer = $writer;
    $this->repository = $repository;
    $this->response = $response;
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
    return $this->request->isRequestedPriceProducts() ?
      str_replace("api_", "mti_api_", static::DISK_FILE_LOCATION) :
      static::DISK_FILE_LOCATION;
  }



  public function getTotalQuantity()
  {
    return $this->writer::$totalQuantity;
  }


  /**
   * loadArray
   *
   * @return DOMDocument
   */
  function loadArray(): DOMDocument
  {

    $transformedList =  $this->request->getProducts()->getTransformedList();

    $arProductXmlIds = array_keys($transformedList);

    $arProperties = $this->repository->getSectionsArray($arProductXmlIds, $this->request->getCatId());

    if (empty($arProperties['SECTIONS']) || empty($arProductXmlIds)) {
      return $this->loadEmpty();
    }

    $arCategories = $this->repository->getTreeList($arProperties['SECTIONS']);

    $arProducts = $this->repository->getList(
      $this->request->isRequestedCategory() ? [$arProductXmlIds[0]] : $arProductXmlIds,
      $arProperties
    );

    $this->writer->bindProductList($this->request->getProducts());

    $this->request->resetProductList($arProductXmlIds);

    return $this->writer->createFile(
      $arProducts,
      $arProperties["PROPERTY_LIST"],
      $this->request->isRequestedDiscontinued() ? [] : $arCategories
    );
  }


  /**
   * loadSection
   *
   * @return DOMDocument
   */
  private function loadSection()
  {
    $arProductXmlIds = $this->repository->getRequestedItemIds([], $this->request->getCatId());
    $this->request->resetProductList($arProductXmlIds);
    return $this->controll($arProductXmlIds);
  }

  /**
   * loadByDate
   *
   * @return DOMDocument
   */
  private function loadByDate()
  {
    $arProductXmlIds = $this->repository->getRequestedItemIds([], 0, $this->request->getDateSince());
    $this->request->resetProductList($arProductXmlIds);
    return $this->controll($arProductXmlIds);
  }


  /**
   * loadByProducts
   *
   * @return DOMDocument
   */
  private function loadByProducts()
  {

    $arProductXmlIds = $this->repository->getRequestedItemIds(array_keys($this->request->getProducts()->getTransformedList()));
    return $this->controll($arProductXmlIds);
  }


  /**
   * controll
   *
   * @param  array $arProductXmlIds
   * @return DOMDocument
   */
  private function controll(array $arProductXmlIds)
  {
    return  count($arProductXmlIds) ?
      $this->loadArray() :  $this->loadEmpty();
  }


  /**
   * emptyGenerator
   *
   * @return Generator
   */
  private function emptyGenerator(): Generator
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
    $request = RequestController::getInstance();
    $model = new Product;
    $writer = new XmlWriterV2($model);
    $repository = MainRepository::getInstance();
    //TO DO: add functionality to create json responses
    $response = XmlResponseController::getInstance();
    $obContent = new static($request, $writer, $repository, $response);
    $view = $obContent->getView();
    $obContent->createResponse($view)
      ->respond();
  }

  /**
   * createResponse
   *
   * @param  mixed $view
   * @return self
   */
  public function createResponse($view): MainController
  {
    $this->response->setData($view, $this->getTotalQuantity());
    return $this;
  }

  protected function respond()
  {
    switch ($this->getTransport()) {
      case static::TRANSPORT_STREAM_FILE:
        $this->response->streamFile();
        break;
      case static::TRANSPORT_STREAM_ZIP:
        $this->response->streamZip();
        break;
      case static::TRANSPORT_STREAM:
        $this->response->streamXml();
        break;
      case static::TRANSPORT_DISK_FILE:
        $this->response->toDisk($this->getFileLocation())->send();
        break;
      default:
        break;
    }
  }

  /**
   * getView
   *
   * @return DOMDocument
   */
  protected function getView(): DOMDocument
  {
    if ($this->request->isInvalid()) {
      $obXml = $this->loadEmpty();
    } elseif ($this->request->isRequestedByDate() || $this->request->isRequestedDiscontinued()) {
      $obXml = $this->loadByDate();
    } else {
      $obXml = $this->request->isRequestedCategory() ? $this->loadSection() : $this->loadByProducts();
    }

    return $obXml;
  }
}
