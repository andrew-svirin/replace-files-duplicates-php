<?php

namespace AndrewSvirin\FileReplace;

use AndrewSvirin\FileReplace\Contracts\CacheStorageInterface;
use AndrewSvirin\FileReplace\Wrappers\DuplicateStreamWrapper;
use AndrewSvirin\FileReplace\Wrappers\ReplacementStreamWrapper;
use AndrewSvirin\FileReplace\Wrappers\ScannerStreamWrapper;

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
    * Directories paths where scan will search duplicates.
    * @var array
    */
   private $dirPaths;

   /**
    * Cache Storage for intermediate data.
    * @var CacheStorageInterface
    */
   private $cacheStorage;

   /**
    * Context for Scanner.
    * @var resource
    */
   private $scannerStreamWrapperContext;

   /**
    * Context for Duplicates.
    * @var resource
    */
   private $duplicateStreamWrapperContext;

   /**
    * Context for Replacements.
    * @var resource
    */
   private $replacementStreamWrapperContext;

   /**
    * ReplacementService constructor.
    * Register streams for working with files, duplicates, replacements.
    * @param array $dirPaths
    * @param CacheStorageInterface $cacheStorage
    */
   public function __construct(array $dirPaths, CacheStorageInterface $cacheStorage)
   {
      $this->dirPaths = $dirPaths;
      $this->cacheStorage = $cacheStorage;
      ScannerStreamWrapper::register('scanner');
      DuplicateStreamWrapper::register('duplicates');
      ReplacementStreamWrapper::register('replacements');
      $this->scannerStreamWrapperContext = ScannerStreamWrapper::createContext($cacheStorage);
      $this->duplicateStreamWrapperContext = DuplicateStreamWrapper::createContext($cacheStorage);
      $this->replacementStreamWrapperContext = ReplacementStreamWrapper::createContext($cacheStorage);
   }

   /**
    * Scans directory on files and prepare index.
    * Update scanned index by new added files on next launches.
    * @param callable|null $filter Callback that filters only necessary files.
    */
   public function scan(callable $filter = null): void
   {
      // TODO: Implements scan.
      if (null !== $filter)
      {
         return;
      }
      fopen('scanner://', 'r', false, $this->scannerStreamWrapperContext);
      return;
   }

   /**
    * Scans index on duplicates and mark point of last scan for scan continuously.
    * Prepare index of duplicates.
    * @return array|null
    */
   public function getDuplicates(): ?array
   {
      // TODO: Implements getDuplicates.
      return [];
   }

   /**
    * Replace duplicates by hard link and update duplicates list.
    */
   public function replaceDuplicatesHard(): bool
   {
      // TODO: Implements replaceDuplicatesHard.
      return true;
   }

   /**
    * Replace duplicates by soft link and update duplicates list.
    */
   public function replaceDuplicatesSoft(): bool
   {
      // TODO: Implements replaceDuplicatesSoft.
      return true;
   }

}