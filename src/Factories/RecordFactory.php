<?php

namespace AndrewSvirin\FileReplace\Factories;

use AndrewSvirin\FileReplace\Models\Record;

/**
 * Class RecordFactory implements producing model instances for @see Record.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RecordFactory
{

   /**
    * Builds new Record instance by output line.
    * @param string $line
    * @return Record
    */
   public static function buildRecordFromLine(string $line): Record
   {
      $record = new Record();
      $record->path = substr($line, 22);
      // Skip fractional part from output line.
      $record->modifiedAt = (int)substr($line, 0, 10);
      return $record;
   }

   /**
    * Builds serialized data from Record.
    * @param Record $record
    * @return string
    */
   public static function buildDataFromRecord(Record $record): string
   {
      return $record->serialize();
   }

   /**
    * Build Record from serialized data.
    * @param string $data
    * @return Record
    */
   public static function buildRecordFromData(string $data): Record
   {
      $record = new Record();
      $record->unserialize($data);
      return $record;
   }

}