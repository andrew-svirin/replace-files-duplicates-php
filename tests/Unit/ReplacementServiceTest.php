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

   /**
    * Copy directory recursively.
    * @param string $src
    * @param string $dst
    */
   private function recurseCopy(string $src, string $dst)
   {
      $dir = opendir($src);
      @mkdir($dst);
      while (false !== ($file = readdir($dir)))
      {
         if (($file != '.') && ($file != '..'))
         {
            if (is_dir($src . '/' . $file))
            {
               $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
            }
            else
            {
               copy($src . '/' . $file, $dst . '/' . $file);
            }
         }
      }
      closedir($dir);
   }

   /**
    * Remove directory recursively.
    * @param string $dir
    */
   private function recurseRemoveDir(string $dir)
   {
      if (is_dir($dir))
      {
         $objects = scandir($dir);
         foreach ($objects as $object)
         {
            if ($object != "." && $object != "..")
            {
               if (is_dir($dir . "/" . $object))
                  $this->recurseRemoveDir($dir . "/" . $object);
               else
                  unlink($dir . "/" . $object);
            }
         }
         rmdir($dir);
      }
   }

   protected function setUp()
   {
      parent::setUp();
      $this->recurseCopy($this->fixtures, $this->data . '/fixtures');
      $dirPaths = [
         $this->data . '/fixtures',
      ];
      $cacheStorage = new UnixFileIndexStorage($this->data . '/index');
      $scanStorage = new UnixScanStorage($dirPaths);
      $this->replacementService = new ReplacementService($scanStorage, $cacheStorage);
   }

   protected function tearDown()
   {
      $this->recurseRemoveDir($this->data . '/fixtures');
      $this->recurseRemoveDir($this->data . '/index');
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
         fseek($fp, 0);
         $firstChar = fgetc($fp);
         fseek($fp, -1, SEEK_END);
         $lastChar = fgetc($fp);
         $fileSize = filesize($file->path);
         $hash = $fileSize . ord($firstChar) . ord($lastChar);
         return $hash;
      }, function (string $hashA = null, string $hashB = null)
      {
         // Compare hashes.
         $result = strnatcmp($hashA, $hashB);
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
      $this->testScan();
      $duplicatesGen = $this->replacementService->findDuplicates();
      while (($records = $duplicatesGen->current()))
      {
         $this->assertTrue(is_array($records));
         $duplicatesGen->next();
      }
   }

   /**
    * @group replace_hard
    */
   public function testReplaceDuplicatesHard()
   {
      $this->testScan();
      $duplicatesGen = $this->replacementService->findDuplicates();
      while (($records = $duplicatesGen->current()))
      {
         $this->assertTrue($this->replacementService->replaceDuplicatesHard($records));
         $duplicatesGen->next();
      }
   }

   /**
    * @group replace_soft
    */
   public function testReplaceDuplicatesSoft()
   {
      $this->testScan();
      $duplicatesGen = $this->replacementService->findDuplicates();
      while (($records = $duplicatesGen->current()))
      {
         $this->assertTrue($this->replacementService->replaceDuplicatesSoft($records));
         $duplicatesGen->next();
      }
   }

   /**
    * @group duplicates_size
    */
   public function testDuplicatesSize()
   {
      $this->testReplaceDuplicatesHard();
      $duplicatesGen = $this->replacementService->findDuplicates();
      $duplicateSize = 0;
      $linkBlock = 1;
      while (($records = $duplicatesGen->current()))
      {
         /* @var $record Record */
         $record = reset($records);
         $stat = stat($record->path);
         if (0 < $stat['blocks'])
         {
            $duplicateSize += ($stat['blocks'] - $linkBlock) * $stat['blksize'];
         }
         $duplicatesGen->next();
      }
      $this->assertTrue($duplicateSize > 0);
   }

}