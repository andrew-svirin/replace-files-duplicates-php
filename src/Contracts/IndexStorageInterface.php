<?php

namespace AndrewSvirin\FileReplace\Contracts;

use AndrewSvirin\FileReplace\Models\Record;

/**
 * Interface CacheStorageInterface implements CacheStorage Interface that provides functions for work with cache.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface IndexStorageInterface
{

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

   /**
    * Get amount of records in the stream.
    * @param string $path
    * @return int
    */
   function countRecords(string $path): int;

   /**
    * Read record hash by position from the stream.
    * @param string $path
    * @param int $position
    * @return string|null
    */
   function readRecordHash(string $path, int $position): ?string;

   /**
    * Write new record on position to the stream.
    * @param string $path
    * @param int $position
    * @param Record $record
    * @return void
    */
   function insertRecord(string $path, int $position, Record $record): void;
}

