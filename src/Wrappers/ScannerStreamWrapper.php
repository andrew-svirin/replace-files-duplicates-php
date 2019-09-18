<?php

namespace AndrewSvirin\FileReplace\Wrappers;

use AndrewSvirin\FileReplace\Wrappers\Traits\StreamTrait;

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

   use StreamTrait;

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