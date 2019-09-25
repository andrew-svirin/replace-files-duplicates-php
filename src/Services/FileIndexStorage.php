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
      // Read line by number from file. Extract hash. Cut delimiter from the end of the line.
      $cmd = sprintf('sed -n %dp %s  | grep -E "(.+)\%s" -o | sed "s/.$//"', $position, $this->filePath($path), self::INDEX_DELIMITER);
      // Read specific line. Extract substring for character. Cut last delimiter character.
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('File readLineForCharacter failed: %s', $cmd));
      }
      $result = !empty($output) ? (int)reset($output) : null;
      return $result;
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
   public function insertRecord(string $path, int $position, Record $record): void
   {
      $recordsAmount = $this->countRecords($path);
      $data = sprintf('%s' . self::INDEX_DELIMITER . '%s', $record->hash, $record->path);
      $output = [];
      $return = [];
      if (0 === $recordsAmount)
      {
         // File was empty and data is first record.
         $cmd = sprintf('echo "%s" >> %s', $data, $this->filePath($path));
      }
      elseif (0 === $position)
      {
         // Prepend new line after line number.
         $cmd = sprintf('sed -i "1i\%s" %s', $data, $this->filePath($path));
      }
      elseif ($recordsAmount < $position)
      {
         // Position is over records amount, And new data should be put in the end of file.
         $cmd = sprintf('sed -i "%da\%s" %s', $recordsAmount, $data, $this->filePath($path));
      }
      else
      {
         // Append new line after line number.
         $cmd = sprintf('sed -i "%da\%s" %s', $position, $data, $this->filePath($path));
      }
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
      $cmd = sprintf('if [ ! -e %s ]; then (mkdir -p %s && touch %s) fi', $filePath, $fileDir, $filePath);
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

