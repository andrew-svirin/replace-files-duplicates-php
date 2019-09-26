<?php

namespace AndrewSvirin\FileReplace\Contracts;

use AndrewSvirin\FileReplace\Models\Record;

/**
 * Interface ScanStorageInterface implements scan storage dirs methods.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface ScanStorageInterface
{

   /**
    * Find files for index in the scanning directories.
    * Order result by timestamp.
    * @param string|null $lastTimestamp Last scan date timestamp with fractional part.
    * @param null $amount
    * @param int $depth Scan files depth.
    * @param int|null $currentTimestamp Current date.
    * @return Record[]
    */
   function findRecords(string $lastTimestamp = null, $amount = null, int $depth = 1, int $currentTimestamp = null): array;

}
