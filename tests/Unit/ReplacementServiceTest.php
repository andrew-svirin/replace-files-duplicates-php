<?php

use AndrewSvirin\FileReplace\Models\Record;
use AndrewSvirin\FileReplace\Storages\UnixScanStorage;
use AndrewSvirin\FileReplace\ReplacementService;
use AndrewSvirin\FileReplace\Services\UnixFileIndexStorage;
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
      $cacheStorage = new UnixFileIndexStorage($this->data);
      $scanStorage = new UnixScanStorage($dirPaths);
      $this->replacementService = new ReplacementService($scanStorage, $cacheStorage);
   }

   /**
    * @group scan
    */
   public function testScan()
   {
      $this->replacementService->scan(function (Record $file)
      {
         // Hash consists from concatenation file size + first byte + last byte.
         $fp = fopen($file->path, 'r');
         fseek($fp, 10);
         $firstChar = fgetc($fp);
         fseek($fp, -10, SEEK_END);
         $lastChar = fgetc($fp);
         $fileSize = filesize($file->path);
         $hash = $fileSize . ord($firstChar) . ord($lastChar);
         return $hash;
      }, function (string $hashA = null, string $hashB = null)
      {
         // Compare hashes.
         $result = $hashA <=> $hashB;
         return $result;
      }, function (Record $file)
      {
         // Filter only txt files for scan.
         $ext = pathinfo($file->path, PATHINFO_EXTENSION);
         return in_array($ext, ['txt']);
      });
      $this->assertTrue(true);
   }

   /**
    * @group duplicates
    */
   public function testFindDuplicates()
   {
      $duplicates = $this->replacementService->findDuplicates();
      while (($duplicate = $duplicates->current()))
      {
         $this->assertTrue(is_array($duplicate));
         $duplicates->next();
      }
   }

}