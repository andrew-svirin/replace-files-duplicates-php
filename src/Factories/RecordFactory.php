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
    * Delimiter between hash and file path for file.
    */
   const FILE_INDEX_DELIMITER = ' ';

   /**
    * Builds new Record instance by output line.
    * @param string $line
    * @return Record
    */
   public static function buildRecordFromOutputLine(string $line): Record
   {
      $record = new Record();
      // Read timestamp with fractional part from output line.
      $record->modifiedAt = substr($line, 0, 21);
      // Read path from output line.
      $record->path = substr($line, 22);
      return $record;
   }

   /**
    * Builds new Record instance by index line.
    * @param string $line
    * @return Record
    */
   public static function buildRecordFromIndexLine(string $line): Record
   {
      $record = new Record();
      $delimiterPosition = strpos($line, self::FILE_INDEX_DELIMITER);
      // Read hash from index line.
      $record->hash = substr($line, 0, $delimiterPosition);
      // Read path from index line.
      $record->path = substr($line, $delimiterPosition + 1);
      return $record;
   }

   /**
    * Builds index line from the Record.
    * @param Record $record
    * @return string
    */
   public static function buildIndexLineFromRecord(Record $record): string
   {
      $line = sprintf('%s' . self::FILE_INDEX_DELIMITER . '%s', $record->hash, $record->path);
      return $line;
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