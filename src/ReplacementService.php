<?php

namespace AndrewSvirin\FileReplace;

use AndrewSvirin\FileReplace\Contracts\CacheStorageInterface;
use AndrewSvirin\FileReplace\Wrappers\ScannerStreamWrapper;
use DateTime;

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
      $this->scannerStreamWrapperContext = ScannerStreamWrapper::createContext($cacheStorage);
   }

   /**
    * Scans directory on files and prepare index.
    * Update scanned index by new added files on next launches.
    * @param callable $indexRule Rule by index determination for save ordered index.
    * @param callable|null $filter Callback that filters only necessary files.
    */
   public function scan(callable $indexRule, callable $filter = null): void
   {
      // TODO: Implements scan.
      // Read last scan date from the stream.
      $lastScanDateHandler = fopen('scanner://index/last-date', 'rb', false, $this->scannerStreamWrapperContext);
      $lastScanTime = fread($lastScanDateHandler, 20);
      fclose($lastScanDateHandler);
      $lastScanDate = DateTime::createFromFormat('U', !empty($lastScanTime) ? (int)$lastScanTime : time());
      if (!($filePaths = $this->findFilePaths($lastScanDate)))
      {
         // Return if not new files found.
         return;
      }
      if (null !== $filter)
      {
         array_filter($filePaths, $filter);
      }
      // Add to index storage indexed files.
      $indexHandler = fopen('scanner://index/data', 'w', false, $this->scannerStreamWrapperContext);
      foreach ($filePaths as $filePath)
      {
         $fileIndex = $indexRule($filePath);
         fwrite($indexHandler, sprintf('%s:%s', $fileIndex, $indexHandler));
      }
      fclose($indexHandler);
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

   /**
    * Find files for index in the scanning directories.
    * @param DateTime|null $lastDateTime Last scan date.
    * @param int $depth Scan files depth.
    * @param DateTime|null $currentDateTime Current date.
    * @return array
    */
   private function findFilePaths(DateTime $lastDateTime = null, int $depth = 1, DateTime $currentDateTime = null): array
   {
      if (null === $currentDateTime)
      {
         $currentDateTime = DateTime::createFromFormat('U', time());
      }
      $args = [];
      if (!empty($depth))
      {
         $args[] = sprintf('-maxdepth %d', (int)$depth);
      }
      if ($lastDateTime && ($minDays = $currentDateTime->diff($lastDateTime)->format('%a')))
      {
         $args[] = sprintf('-mtime %d', (int)$minDays);
      }
      $command = sprintf('find %s %s', implode(' ', $this->dirPaths), implode(' ', $args));
      $output = [];
      exec($command, $output);
      return $output;
   }

}