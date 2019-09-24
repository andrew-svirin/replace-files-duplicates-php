<?php

namespace AndrewSvirin\FileReplace\Wrappers;

use AndrewSvirin\FileReplace\Contracts\IndexStorageInterface;
use AndrewSvirin\FileReplace\Factories\RecordFactory;

/**
 * Class FileReaderWrapper implements working with File paths and uses storage cache.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ScannerStreamWrapper
{

   /**
    * Is registered stream wrapper.
    * @var bool
    */
   protected static $isRegistered = false;

   /**
    * Wrapper name.
    * @var string
    */
   protected static $wrapperName;

   /**
    * Implements $context for StreamWrapper.
    * @var resource
    */
   public $context;

   /**
    * Cache storage.
    * @var IndexStorageInterface
    */
   protected $cacheStorage;

   /**
    * End of stream indicator.
    * @var bool
    */
   protected $eof = false;

   /**
    * Stream path.
    * @var string
    */
   private $path;

   /**
    * Function for compare line hashes. Function must return -1|0|1.
    * @var callable
    */
   private $indexComparator;

   /**
    * Function for generate line hash. Function must return string.
    * @var callable
    */
   private $indexGenerator;

   /**
    * Register stream wrapper.
    * @param string $wrapperName
    */
   public static function register(string $wrapperName): void
   {
      if (!static::$isRegistered)
      {
         stream_wrapper_register($wrapperName, get_class());
         static::$isRegistered = true;
         static::$wrapperName = $wrapperName;
      }
   }

   /**
    * Create context for stream wrapper.
    * @param array $context
    * @return resource
    */
   public static function createContext(array $context)
   {
      return stream_context_create([static::$wrapperName => $context]);
   }

   /**
    * Extract from context properties and set it to instance property.
    */
   protected function setContext(): void
   {
      $options = stream_context_get_options($this->context);
      $this->cacheStorage = $options[self::$wrapperName]['cacheStorage'];
      $this->indexComparator = $options[self::$wrapperName]['indexComparator'] ?? null;
      $this->indexGenerator = $options[self::$wrapperName]['indexGenerator'] ?? null;
   }

   /**
    * Implements stream_open() for StreamWrapper.
    * @param string $path
    * @param $mode
    * @param $options
    * @param $opened_path
    * @return bool
    */
   public function stream_open($path, $mode, $options, &$opened_path): bool
   {
      if (!$this->context)
      {
         return false;
      }
      $this->path = $path;
      $this->setContext();
      $this->cacheStorage->prepare($this->relPath());
      return true;
   }

   /**
    * Implements stream_read() for StreamWrapper.
    * @param $count
    * @return string
    */
   public function stream_read($count): string
   {
      if ($this->eof || !$count)
      {
         // If end of stream resource or read characters less than 0.
         return '';
      }
      if ('' === ($result = $this->cacheStorage->read($this->relPath(), $count)))
      {
         // If read empty string, then stream resource is end.
         $this->eof = true;
      }
      return $result;
   }

   /**
    * Implements stream_write() for StreamWrapper.
    * @param string $data
    * @return int Must return size of stored data, otherwise will called again.
    */
   public function stream_write(string $data): int
   {
      $record = RecordFactory::buildRecordFromData($data);
      $record->hash = call_user_func_array($this->indexGenerator, [$record]);
      $position = $this->findWritePosition($record->hash);
      $this->cacheStorage->appendRecord($this->relPath(), $position, $record);
      return strlen($data);
   }

   /**
    * Find in the index stream resource position for insertion.
    * @param string $recordHash
    * @return int
    */
   private function findWritePosition(string $recordHash): int
   {
      $position = 1;
      // Count records int the stream.
      $recordsAmount = $this->cacheStorage->countRecords($this->relPath());
      if (0 === $recordsAmount)
      {
         // Stream not created yet and position is on the beginning.
         return $position;
      }
      // Set the left pointer to 1.
      $left = $position;
      // Set the right pointer to the length of the array.
      $right = $recordsAmount;
      while ($left <= $right)
      {
         // Set the initial midpoint to the rounded down value of half the length of the array.
         $midPoint = (int)floor(($left + $right) / 2);
         $midHash = $this->cacheStorage->readRecordHash($this->relPath(), $midPoint);
         // Compare line hashes.
         $compHashes = call_user_func_array($this->indexComparator, [$recordHash, $midHash]);
         if (1 === $compHashes)
         {
            // The midpoint line hash is less than the line hash.
            $left = $midPoint + 1;
            // Set to position left position.
            $position = $left;
         }
         elseif (-1 === $compHashes)
         {
            // The midpoint line hash is greater than the line hash.
            $right = $midPoint - 1;
            // Set to position right position.
            $position = $right;
         }
         else
         {
            // This is the key we are looking for.
            $position = $midPoint;
            break;
         }
      }
      // Position holds the last compared place and also position for next insertion.
      return $position;
   }

   /**
    * Implements stream_eof() for StreamWrapper.
    * @return bool
    */
   public function stream_eof(): bool
   {
      return $this->eof;
   }

   /**
    * Get relative path. That is without schema prefix.
    * @return string
    */
   private function relPath(): string
   {
      return str_replace(self::$wrapperName . '://', '', $this->path);
   }

}