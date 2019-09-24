<?php

namespace AndrewSvirin\FileReplace\Services;

use AndrewSvirin\FileReplace\Contracts\FileIndexStorageInterface;
use AndrewSvirin\FileReplace\Models\Record;

/**
 * Class FileCacheStorage implements CacheStorage in the file.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class FileIndexStorage implements FileIndexStorageInterface
{

   /**
    * Delimiter between hash and file path.
    */
   const INDEX_DELIMITER = ':';

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
    * Get path to the file in the dir.
    * @param $path
    * @return string
    */
   private function filePath($path): string
   {
      return $this->dirPath . '/' . $path;
   }

   /**
    * {@inheritdoc}
    */
   public function getDirPath(): string
   {
      return $this->dirPath;
   }

   /**
    * {@inheritdoc}
    */
   public function readRecordHash(string $path, int $position): ?string
   {
      $output = [];
      $return = [];
      $cmd = sprintf('sed "%d!d" < %s  | grep -E "(.+)\%s" -o | cut -c 1', $position, $this->filePath($path), self::INDEX_DELIMITER);
      // Read specific line. Extract substring for character. Cut last delimiter character.
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('File readLineForCharacter failed: %s', $cmd));
      }
      return !empty($output) ? (int)reset($output) : null;
   }

   /**
    * Get number of the lines in the index file.
    * {@inheritdoc}
    */
   public function countRecords(string $path): int
   {
      $output = [];
      $return = [];
      $cmd = sprintf('wc -l %s | grep -E "([0-9]+) " -o | cut -c 1', $this->filePath($path));
      // Count lines number in the file. Extract amount value by space suffix. Cut last delimiter character.
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('File countRecords failed: %s', $cmd));
      }
      return !empty($output) ? (int)reset($output) : 0;
   }

   /**
    * Write to specific position in the file.
    * {@inheritdoc}
    */
   public function appendRecord(string $path, int $position, Record $record): void
   {
      $data = sprintf('%s' . self::INDEX_DELIMITER . '%s', $record->hash, $record->path);
      $output = [];
      $return = [];
      $cmd = sprintf('sed -i "%di\%s" %s', $position, $data, $this->filePath($path));
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('File writeToPosition failed: %s', $cmd));
      }
      return;
   }

   /**
    * {@inheritdoc}
    */
   public function prepare(string $path): void
   {
      $output = [];
      $return = [];
      $filePath = $this->filePath($path);
      $fileDir = dirname($filePath);
      // If file does not exists, then create dir and file.
      $cmd = sprintf('if [ ! -e %s ]; then (mkdir -p %s && touch %s && echo "" > %s) fi', $filePath, $fileDir, $filePath, $filePath);
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('File prepare failed: %s', $cmd));
      }
      return;
   }

   /**
    * {@inheritdoc}
    */
   public function read(string $path, int $count): string
   {
      $output = [];
      $return = [];
      $cmd = sprintf(' head -c %d %s', $count, $this->filePath($path));
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('File read failed: %s', $cmd));
      }
      return !empty($output) ? reset($output) : '';
   }
}

