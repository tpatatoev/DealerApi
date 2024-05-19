<?php

namespace MTI\DealerApi\V2\Controllers;

use DOMDocument;
use MTI\Base\Singleton;

/**
 * ResponseController
 */
class XmlResponseController extends Singleton
{
  protected DOMDocument $response;
  protected $quantity;
  protected $fileSize;
  protected string $link;

  protected function __construct()
  {
  }

  /**
   * setData
   *
   * @param  DOMDocument $xml
   * @param  int $quantity
   * @return void
   */
  public function setData(DOMDocument $xml, $quantity): void
  {
    $this->response = $xml;
    $this->quantity = $quantity;
  }

  public function streamXml()
  {
    header("content-type: application/xml; charset=UTF-8");
    echo $this->response->saveXML(), "\n";
    return;
  }

  public function streamFile()
  {
    header("content-type: application/octet-stream");
    header('Content-Disposition: attachment;  filename="mti_api_goods.xml"');
    $this->response->save('php://output');
  }

  public function streamZip()
  {
  }


  public function toDisk(string $pathToFile)
  {
    $fileName = $_SERVER['DOCUMENT_ROOT'] . $pathToFile;
    $this->fileSize = $this->response->save($fileName);
    $this->link = $_SERVER['SERVER_NAME'] . $pathToFile;


    // if (self::$needsZip) {
    // 	$packarc = CBXArchive::GetArchive($_SERVER["DOCUMENT_ROOT"] . "/upload/xml_update/export/mti_api_goods.zip");
    // 	$packarc->SetOptions(array(
    // 		"REMOVE_PATH" => $_SERVER["DOCUMENT_ROOT"] . "/upload/xml_update/export/",
    // 	));
    // 	$packarc->Pack([$fileName]);
    // }

    return $this;
  }

  public function send()
  {
    $mbSize = round($this->fileSize / pow(1024, 2));
    $size = $mbSize > 0 ? $mbSize : round($this->fileSize / 1024);
    $units = $mbSize > 0 ? " МБайт" : " KБайт";
    $response = [
      'responseText' => $this->quantity,
      'size' => 'Записано: ' . $size . $units,
      'memory' => 'Использовано памяти: ' . memory_get_usage(true) / 1024 / 1024,
      'downloadPath' => 'https://' . $this->link
    ];
    header("Content-Type: application/json");
    echo json_encode(
      $response,
      JSON_UNESCAPED_UNICODE |
        JSON_PRETTY_PRINT
    );
  }
}
