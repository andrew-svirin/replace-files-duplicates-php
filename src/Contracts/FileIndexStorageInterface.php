<?php

namespace AndrewSvirin\FileReplace\Contracts;

/**
 * Interface CacheStorageInterface implements CacheStorage Interface that provides functions for work with cache.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface FileIndexStorageInterface extends IndexStorageInterface
{

   /**
    * Get directory path for cache storage.
    * @return string
    */
   function getDirPath(): string;

}

