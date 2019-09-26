<?php

namespace AndrewSvirin\FileReplace\Storages;

use AndrewSvirin\FileReplace\Contracts\ScanStorageInterface;
use AndrewSvirin\FileReplace\Factories\RecordFactory;

/**
 * Class UnixScanStorage implements storage for scan dirs.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class UnixScanStorage implements ScanStorageInterface
{

   /**
    * Directories for scan.
    * @var array
    */
   private $dirPaths;

   public function __construct(array $dirPaths)
   {
      $this->dirPaths = $dirPaths;
   }

   /**
    * {@inheritdoc}
    */
   public function findRecords(string $lastTimestamp = null, $amount = null, int $depth = 1, int $currentTimestamp = null): array
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
         $minSeconds = $currentTimestamp - $lastTimestamp;
         $minMinutes = 1 / 60 * $minSeconds;
         // Last N minutes file was modified.
         $args[] = sprintf('-cmin -%d', ceil($minMinutes));
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
         if (1 !== strnatcmp($record->modifiedAt, $lastTimestamp))
         {
            continue;
         }
         $result[] = $record;
      }
      return $result;
   }

}