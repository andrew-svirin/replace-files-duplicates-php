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
   }

   /**
    * Scans directory on files and prepare index.
    * Update scanned index by new added files on next launches.
    * @param callable $indexGenerator Rule for index determination for save index.
    * @param callable $indexComparator Rule for index comparision for save index.
    * @param callable|null $filter Callback that filters only necessary files.
    */
   public function scan(callable $indexGenerator, callable $indexComparator, callable $filter = null): void
   {
      // TODO: Implements scan.
      $lastScanDate = $this->readLastScanDate();
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
      $indexHandleContext = ScannerStreamWrapper::createContext([
         'cacheStorage' => $this->cacheStorage,
         'indexComparator' => $indexComparator,
         'indexGenerator' => $indexGenerator,
      ]);
      $indexHandle = fopen('scanner://index/data', 'w', false, $indexHandleContext);
      foreach ($filePaths as $filePath)
      {
         fwrite($indexHandle, $filePath);
      }
      fclose($indexHandle);
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
         // Max depth for search in children directories.
         $args[] = sprintf('-maxdepth %d', (int)$depth);
      }
      if ($lastDateTime && ($minDays = $currentDateTime->diff($lastDateTime)->format('%a')))
      {
         // Last N days file was modified.
         $args[] = sprintf('-mtime %d', (int)$minDays);
      }
      // Find only files.
      $args[] = '-type f';
      $cmd = sprintf('find %s %s', implode(' ', $this->dirPaths), implode(' ', $args));
      $output = [];
      $return = [];
      exec($cmd, $output, $return);
      if(!empty($return)){
         trigger_error(sprintf('Find FilePaths failed: %s', $cmd));
      }
      return $output;
   }

   /**
    * Read from the file last scan date and format result.
    * @return DateTime
    */
   private function readLastScanDate(): DateTime
   {
      $lastScanDateContext = ScannerStreamWrapper::createContext([
         'cacheStorage' => $this->cacheStorage,
      ]);
      $lastScanDateHandle = fopen('scanner://index/last-date', 'rb', false, $lastScanDateContext);
      // Read timestamp from the file.
      $lastScanTime = fread($lastScanDateHandle, 20);
      fclose($lastScanDateHandle);
      // Format read string to DateTime.
      $lastScanDate = DateTime::createFromFormat('U', !empty($lastScanTime) ? (int)$lastScanTime : time());
      return $lastScanDate;
   }

}