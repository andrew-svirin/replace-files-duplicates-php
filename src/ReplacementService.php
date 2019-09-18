<?php

namespace AndrewSvirin\FileReplace;

use AndrewSvirin\FileReplace\Contracts\CacheStorageInterface;

/**
 * File Replacement Service.
 *
 * Uses cache for indexing scanned results for files and duplicates that helps to launch scan faster for next time.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ReplacementService
{

   /**
    * @var array
    */
   private $paths;

   /**
    * @var CacheStorageInterface
    */
   private $cacheStorage;

   public function __construct(array $paths, CacheStorageInterface $cacheStorage)
   {
      $this->paths = $paths;
      $this->cacheStorage = $cacheStorage;
   }

   /**
    * Scans directory on files and prepare index.
    * Update scanned index by new added files on next launches.
    * @param callable|null $filter Callback that filters only necessary files.
    */
   public function scan(callable $filter = null): void
   {
      if (null !== $filter)
      {
         return;
      }
      return;
   }

   /**
    * Scans index on duplicates and mark point of last scan for scan continuously.
    * Prepare index of duplicates.
    * @return array|null
    */
   public function getDuplicates(): ?array
   {
      return [];
   }

   /**
    * Replace duplicates by hard link.
    */
   public function replaceDuplicatesHard(): bool
   {
      return true;
   }

   /**
    * Replace duplicates by soft link.
    */
   public function replaceDuplicatesSoft(): bool
   {
      return true;
   }

}