<?php

namespace AndrewSvirin\FileReplace\Wrappers\Traits;

use AndrewSvirin\FileReplace\Contracts\CacheStorageInterface;

/**
 * File StreamTrait implements stream basic methods.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait StreamTrait
{

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

}