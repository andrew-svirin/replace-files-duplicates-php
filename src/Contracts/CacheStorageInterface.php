<?php

namespace AndrewSvirin\FileReplace\Contracts;

/**
 * File CacheStorageInterface implements CacheStorage Interface that provides functions for work with cache.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface CacheStorageInterface
{

   /**
    * Stream resource exist.
    * @param string $path
    * @return bool
    */
   function exists(string $path): bool;

   /**
    * Read string line for met character from the stream.
    * @param string $path
    * @param int $position
    * @param string $char
    * @return string|null
    */
   function readLineForCharacter(string $path, int $position, string $char): ?string;

   /**
    * Get amount of records in the stream.
    * @param string $path
    * @return int
    */
   function countRecords(string $path): int;

   /**
    * Write new record on position to the stream.
    * @param string $path
    * @param int $position
    * @param string $data
    * @return void
    */
   function writeToPosition(string $path, int $position, string $data): void;

   /**
    * Prepare stream for read/write operation.
    * @param string $path
    */
   function prepare(string $path): void;

   /**
    * Read N bytes from the stream.
    * @param string $path
    * @param int $count
    * @return string
    */
   function read(string $path, int $count);
}

