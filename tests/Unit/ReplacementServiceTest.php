<?php

use AndrewSvirin\FileReplace\ReplacementService;
use AndrewSvirin\FileReplace\Services\FileCacheStorage;
use PHPUnit\Framework\TestCase;

/**
 * Class ReplacementServiceTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ReplacementServiceTest extends TestCase
{

   var $data = __DIR__ . '/../_data';
   var $fixtures = __DIR__ . '/../_fixtures';

   /**
    * @var ReplacementService
    */
   private $replacementService;

   public function setUp()
   {
      parent::setUp();
      $dirPaths = [
         $this->fixtures,
      ];
      $cacheStorage = new FileCacheStorage($this->data);
      $this->replacementService = new ReplacementService($dirPaths, $cacheStorage);
   }

   /**
    * @group scan
    */
   public function testScan()
   {
      $this->replacementService->scan(function (string $filePath)
      {
         // Index consists from concatenation file size + first byte + last byte.
         $fp = fopen($filePath, 'r');
         fseek($fp, 0);
         $firstByte = fgets($fp, 1);
         fseek($fp, -1, SEEK_END);
         $lastByte = fgets($fp, 1);
         $fileSize = filesize($filePath);
         return $fileSize . $firstByte . $lastByte;
      }, function ($a, $b)
      {
         return 0;
      }, function (string $filePath)
      {
         // Filter only txt files.
         $ext = pathinfo($filePath, PATHINFO_EXTENSION);
         return in_array($ext, ['txt']);
      });
      $this->assertTrue(true);
   }

   /**
    * @depends testScan
    * @group duplicates
    */
   public function testGetDuplicates()
   {

   }

}