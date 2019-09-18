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
      $this->replacementService->scan();
      $this->assertTrue(true);
   }

}