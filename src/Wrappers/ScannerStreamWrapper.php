<?php

namespace AndrewSvirin\FileReplace\Wrappers;

use AndrewSvirin\FileReplace\Contracts\CacheStorageInterface;

/**
 * File FileReaderWrapper implements working with Files and uses cache.
 *
 * Use `find . -maxdepth 1 -mtime -1` for scan dir on files, where `-mtime -1` ~ 24 hours ago.
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
    * Register stream wrapper.
    * @param string $wrapperName
    */
   public static function register($wrapperName): void
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
    * @param CacheStorageInterface $cacheStorage
    * @return resource
    */
   public static function createContext(CacheStorageInterface $cacheStorage)
   {
      return stream_context_create([static::$wrapperName => ['cacheStorage' => $cacheStorage]]);
   }

   /**
    * Extract from context cache storage and set it to instance property.
    */
   protected function setCacheStorageFromContext(): void
   {
      $options = stream_context_get_options($this->context);
      $this->cacheStorage = $options[self::$wrapperName]['cacheStorage'];
   }

   /**
    * Implements stream_open() for StreamWrapper.
    * @param $path
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
      $this->setCacheStorageFromContext();
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
    * Implements stream_eof() for StreamWrapper.
    * @return bool
    */
   public function stream_eof(): bool
   {
      // TODO: Implements stream_eof.
      return $this->eof;
   }

}