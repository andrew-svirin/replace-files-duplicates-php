<?php

namespace AndrewSvirin\FileReplace;

use AndrewSvirin\FileReplace\Contracts\IndexStorageInterface;
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
    * Directories paths where scan will search duplicates.
    * @var array
    */
   private $dirPaths;

   /**
    * Cache Storage for intermediate data.
    * @var IndexStorageInterface
    */
   private $cacheStorage;

   /**
    * TODO: Implement find, sed, wc, grep, cut, head, sort aliases.
    * ReplacementService constructor.
    * Register streams for working with files, duplicates, replacements.
    * @param array $dirPaths
    * @param IndexStorageInterface $cacheStorage
    */
   public function __construct(array $dirPaths, IndexStorageInterface $cacheStorage)
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
      $lastTimestamp = $this->readLastScanRecordModifiedAt();
      if (!($records = $this->findRecords($lastTimestamp)))
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
         // Stream works with string only.
         $data = RecordFactory::buildDataFromRecord($record);
         fwrite($indexHandle, $data);
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
         $data = fgets($indexHandle);
         $currentRecord = RecordFactory::buildRecordFromData($data);
         if (null === $prevRecord)
         {
            $prevRecord = $currentRecord;
            continue;
         }
         if ($prevRecord->hash === $currentRecord->hash)
         {
            yield [$prevRecord, $currentRecord];
         }
         $prevRecord = $currentRecord;
      }
      yield null;
   }

   /**
    * Replace duplicates by hard link and update duplicates list.
    * @param array $duplicates
    * @return bool
    */
   public function replaceDuplicatesHard(array $duplicates): bool
   {
      // TODO: Implement replaceDuplicatesHard.
      return isset($duplicates);
   }

   /**
    * Replace duplicates by soft link and update duplicates list.
    * @param array $duplicates
    * @return bool
    */
   public function replaceDuplicatesSoft(array $duplicates): bool
   {
      // TODO: Implement replaceDuplicatesSoft.
      return isset($duplicates);
   }

   /**
    * Find files for index in the scanning directories.
    * Order result by timestamp.
    * @param string|null $lastTimestamp Last scan date timestamp with fractional part.
    * @param null $amount
    * @param int $depth Scan files depth.
    * @param int|null $currentTimestamp Current date.
    * @return Record[]
    */
   private function findRecords(string $lastTimestamp = null, $amount = null, int $depth = 1, int $currentTimestamp = null): array
   {
      if (null === $currentTimestamp)
      {
         $currentTimestamp = time();
      }
      $args = [];
      if (!empty($depth))
      {
         // Max depth for search in children directories.
         $args[] = sprintf('-maxdepth %d', (int)$depth);
      }
      if (null !== $lastTimestamp)
      {
         $minSeconds = $currentTimestamp - (int)$lastTimestamp;
         // Last N seconds file was modified.
         $args[] = sprintf('-mtime -%ds', $minSeconds);
      }
      // Find only files.
      $args[] = '-type f';
      // Record displays: `Timestamp Path` format. And make result ordered DESC.
      $args[] = '-printf "\n%T@ %p"';
      // Sort result by first column ascending.
      $args[] = ' | sort -n -k 1';
      if (null !== $amount)
      {
         // Filter that output limited amount of records.
         $args[] = sprintf(' | head -n %d', $amount + 1);
      }
      $cmd = sprintf('find %s %s', implode(' ', $this->dirPaths), implode(' ', $args));
      $output = [];
      $return = [];
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('Find FilePaths failed: %s', $cmd));
      }
      $result = [];
      unset($output[0]);
      foreach ($output as $line)
      {
         $record = RecordFactory::buildRecordFromOutputLine($line);
         // To result can come lines with identical timestamp but different fractional part, thus ignore processed.
         if ($record->modifiedAt < $lastTimestamp)
         {
            continue;
         }
         $result[] = $record;
      }
      return $result;
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