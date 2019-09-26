<?php

namespace AndrewSvirin\FileReplace;

use AndrewSvirin\FileReplace\Contracts\IndexStorageInterface;
use AndrewSvirin\FileReplace\Contracts\ScanStorageInterface;
use AndrewSvirin\FileReplace\Factories\RecordFactory;
use AndrewSvirin\FileReplace\Models\Record;
use AndrewSvirin\FileReplace\Wrappers\ScannerStreamWrapper;

/**
 * Class Replacement Service.
 *
 * Uses cache for indexing scanned results for files and duplicates that helps to launch scan faster for next time.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ReplacementService
{

   /**
    * Directories storage where scan will search duplicates.
    * @var ScanStorageInterface
    */
   private $scanStorage;

   /**
    * Cache Storage for intermediate data.
    * @var IndexStorageInterface
    */
   private $cacheStorage;

   /**
    * TODO: Implement find, sed, wc, grep, cut, head, sort aliases.
    * ReplacementService constructor.
    * Register streams for working with files, duplicates, replacements.
    * @param ScanStorageInterface $scanStorage
    * @param IndexStorageInterface $cacheStorage
    */
   public function __construct(ScanStorageInterface $scanStorage, IndexStorageInterface $cacheStorage)
   {
      $this->scanStorage = $scanStorage;
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
      $lastTimestamp = $this->readLastScanRecordModifiedAt();
      if (!($records = $this->scanStorage->findRecords($lastTimestamp)))
      {
         // Return if not new files found.
         return;
      }
      // TODO: Implement stream filter
      if (null !== $filter)
      {
         array_filter($records, $filter);
      }
      // Add to index storage indexed files.
      $indexHandleContext = ScannerStreamWrapper::createContext([
         'cacheStorage' => $this->cacheStorage,
         'indexComparator' => $indexComparator,
         'indexGenerator' => $indexGenerator,
      ]);
      $indexHandle = fopen('scanner://index/data', 'wi', false, $indexHandleContext);
      foreach ($records as $record)
      {
         // Stream works with string only and object must be serialized to string.
         fwrite($indexHandle, RecordFactory::buildDataFromRecord($record));
         $this->writeLastScanRecordModifiedAt($record->modifiedAt);
      }
      fclose($indexHandle);
      return;
   }

   /**
    * Scans index stream on records duplicates.
    * @return \Generator|Record[][]|null
    */
   public function findDuplicates()
   {
      $indexHandleContext = ScannerStreamWrapper::createContext([
         'cacheStorage' => $this->cacheStorage,
      ]);
      $indexHandle = fopen('scanner://index/data', 'ri', false, $indexHandleContext);
      $prevRecord = null;
      while (!feof($indexHandle))
      {
         $currentRecord = RecordFactory::buildRecordFromData(fgets($indexHandle));
         if (null === $prevRecord)
         {
            // Nothing compare yet.
            $prevRecord = $currentRecord;
            continue;
         }
         if ($prevRecord->hash === $currentRecord->hash)
         {
            // Compare hashes for previous and current records.
            yield [$prevRecord, $currentRecord];
         }
         $prevRecord = $currentRecord;
      }
      yield null;
   }

   /**
    * Replace duplicates by hard link.
    * @param array|Record[] $records
    * @return bool
    */
   public function replaceDuplicatesHard(array $records): bool
   {
      $original = array_shift($records);
      $status = true;
      while (($duplicate = array_shift($records)))
      {
         $status = $status && $this->scanStorage->replaceByHardLink($original->path, $duplicate->path);
      }
      return $status;
   }

   /**
    * Replace duplicates by soft link.
    * @param array|Record[] $records
    * @return bool
    */
   public function replaceDuplicatesSoft(array $records): bool
   {
      $original = array_shift($records);
      $status = true;
      while (($duplicate = array_shift($records)))
      {
         $status = $status && $this->scanStorage->replaceBySoftLink($original->path, $duplicate->path);
      }
      return $status;
   }

   /**
    * Read from the stream last scanned record modified at datetime with fractional part and format result.
    * @return string|null
    */
   private function readLastScanRecordModifiedAt(): ?string
   {
      $lastTimestampContext = ScannerStreamWrapper::createContext([
         'cacheStorage' => $this->cacheStorage,
      ]);
      $lastTimestampHandle = fopen('scanner://index/last-date', 'r', false, $lastTimestampContext);
      // Read timestamp with fractional part from the file.
      $lastTimestamp = fread($lastTimestampHandle, 21);
      fclose($lastTimestampHandle);
      // Format read string to DateTime.
      $result = !empty($lastTimestamp) ? $lastTimestamp : null;
      return $result;
   }

   /**
    * Write to stream last scan datetime with fractional part.
    * @param string $modifiedAt
    */
   private function writeLastScanRecordModifiedAt(string $modifiedAt)
   {
      $lastTimestampContext = ScannerStreamWrapper::createContext([
         'cacheStorage' => $this->cacheStorage,
      ]);
      $lastTimestampHandle = fopen('scanner://index/last-date', 'w', false, $lastTimestampContext);
      fwrite($lastTimestampHandle, $modifiedAt);
      fclose($lastTimestampHandle);
   }

}