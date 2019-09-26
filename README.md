# replace-files-dupliactes-php [![Build Status](https://travis-ci.org/andrew-svirin/replace-files-duplicates-php.svg?branch=master)](https://travis-ci.org/andrew-svirin/replace-files-duplicates-php)
Replace files duplicates by links.

# Overview
Script oriented to scan directories for files those are equal and replace newest by hard or soft link on older one.
Hard link allow to remove parent file without impact on linked instance, but modification of file or linked instance have effect on both.
Useful tool for decrease size of storage by removing copies.

# Usage
Define Storage for cache and service.
```php
      $dirPaths = [__DIR_PATH_FOR_SCAN__];
      $cacheStorage = new FileIndexStorage(__DIR_PATH_FOR_CACHE_STORAGE__);
      $replacementService = new ReplacementService($dirPaths, $cacheStorage);
```
Scan directories for build index.
```php
      $replacementService->scan(function (Record $file)
      {
         // Hash consists from concatenation file size + first byte + last byte.
         $fp = fopen($file->path, 'r');
         fseek($fp, 10);
         $firstChar = fgetc($fp);
         fseek($fp, -10, SEEK_END);
         $lastChar = fgetc($fp);
         $fileSize = filesize($file->path);
         $hash = $fileSize . ord($firstChar) . ord($lastChar);
         return $hash;
      }, function (string $hashA = null, string $hashB = null)
      {
         // Compare hashes.
         $result = strnatcmp($hashA, $hashB);
         return $result;
      }, function (Record $file)
      {
         // Filter only txt files for scan.
         $ext = pathinfo($file->path, PATHINFO_EXTENSION);
         return in_array($ext, ['txt']);
      });
```
Find duplicates after scan and replace by hard link.
```php
      $duplicatesGen = $replacementService->findDuplicates();
      while (($records = $duplicatesGen->current()))
      {
         $replacementService->replaceDuplicatesHard($records);
         $duplicatesGen->next();
      }
```
