<?php

namespace AndrewSvirin\FileReplace\Services;

use AndrewSvirin\FileReplace\Contracts\FileCacheStorageInterface;
use DateTime;
use DateTimeInterface;

/**
 * File FileCacheStorage implements CacheStorage in the file.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class FileCacheStorage implements FileCacheStorageInterface
{

   /**
    * Path to storage dir.
    * @var string
    */
   private $dirPath;

   public function __construct(string $dirPath)
   {
      $this->dirPath = $dirPath;
   }

   /**
    * {@inheritdoc}
    */
   public function getLastScanDateTime(): DateTimeInterface
   {
      // TODO: Implement getLastScanDateTime() method.
      return DateTime::createFromFormat('U', time());
   }

   /**
    * {@inheritdoc}
    */
   public function getDirPath(): string
   {
      return $this->dirPath;
   }
}

