<?php

namespace AndrewSvirin\FileReplace\Contracts;

use AndrewSvirin\FileReplace\Models\Record;

/**
 * Interface CacheStorageInterface implements CacheStorage Interface that provides functions for work with streams.
 * Represents methods to work with indexed stream.
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
    * Get stream size.
    * @param string $path
    * @return mixed
    */
   function size(string $path): int;

   /**
    * Read N bytes from the stream.
    * @param string $path
    * @param int $count
    * @return string
    */
   function read(string $path, int $count);

   /**
    * Write N bytes to the stream. Overwrites the stream.
    * @param string $path
    * @param string $data
    * @param int|null $count
    * @return mixed
    */
   function write(string $path, string $data, int $count = null);

   /**
    * Get amount of records in the indexed stream.
    * @param string $path
    * @return int
    */
   function countRecords(string $path): int;

   /**
    * Read record by position from the indexed stream.
    * @param string $path
    * @param int $position
    * @return Record|null
    */
   public function readRecord(string $path, int $position);

   /**
    * Write new record on position to the indexed stream.
    * @param string $path
    * @param int $position
    * @param Record $record
    * @return void
    */
   function insertRecord(string $path, int $position, Record $record): void;
}

