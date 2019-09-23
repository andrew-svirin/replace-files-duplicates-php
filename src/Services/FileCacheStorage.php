<?php

namespace AndrewSvirin\FileReplace\Services;

use AndrewSvirin\FileReplace\Contracts\FileCacheStorageInterface;

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
   public function exists(string $path): bool
   {
      return file_exists($this->filePath($path));
   }

   /**
    * {@inheritdoc}
    */
   public function readLineForCharacter(string $path, int $position, string $char): ?string
   {
      $output = [];
      $return = [];
      $cmd = sprintf('sed "%d!d" < %s  | grep -E "(.+)\%s" -o | cut -c 1', $position, $this->filePath($path), $char);
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
   public function writeToPosition(string $path, int $position, string $data): void
   {
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
      $cmd = sprintf('mkdir -p %s && touch %s', $fileDir, $filePath);
      exec($cmd, $output, $return);
      if (!empty($return))
      {
         trigger_error(sprintf('File prepare failed: %s', $cmd));
      }
      return;
   }
}

