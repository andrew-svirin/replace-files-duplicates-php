<?php

namespace AndrewSvirin\FileReplace\Wrappers;

use AndrewSvirin\FileReplace\Contracts\CacheStorageInterface;

/**
 * File FileReaderWrapper implements working with Files and uses cache.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ScannerStreamWrapper
{

   /**
    * Delimiter between hash and file path.
    */
   const INDEX_DELIMITER = ':';

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
    * @var CacheStorageInterface
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
      // TODO: Implements stream_open.
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
      // TODO: Implements stream_read.
      if ($this->eof || !$count)
      {
         return '';
      }
      return '';
   }

   /**
    * Implements stream_write() for StreamWrapper.
    * @param string $filePath
    * @return int
    */
   public function stream_write(string $filePath): int
   {
      $lineHash = call_user_func_array($this->indexGenerator, [$filePath]);
      $position = $this->findWritePosition($lineHash);
      $data = sprintf('%s' . self::INDEX_DELIMITER . '%s', $lineHash, $filePath);
      $this->cacheStorage->writeToPosition($this->relPath(), $position, $data);
      return strlen($filePath);
   }

   /**
    * Find in the index file position for insertion.
    * @param string $lineHash
    * @return int
    */
   private function findWritePosition(string $lineHash): int
   {
      $position = 1;
      // Open stream in read mode.
      if (!$this->cacheStorage->exists($this->relPath()))
      {
         // Stream not created yet and position is on the beginning.
         return $position;
      }
      $recordsAmount = $this->cacheStorage->countRecords($this->relPath());
      // Set the left pointer to 0.
      $left = 0;
      // Set the right pointer to the length of the array -1.
      $right = $recordsAmount - 1;
      while ($left <= $right)
      {
         // Set the initial midpoint to the rounded down value of half the length of the array.
         $midPoint = (int)floor(($left + $right) / 2);
         $midLineHash = $this->cacheStorage->readLineForCharacter($this->relPath(), $midPoint, self:: INDEX_DELIMITER);
         // Compare line hashes.
         $compHashes = call_user_func_array($this->indexComparator, [$lineHash, $midLineHash]);
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
      // TODO: Implements stream_eof.
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