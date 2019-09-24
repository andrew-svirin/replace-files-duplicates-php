<?php

namespace AndrewSvirin\FileReplace\Models;

use Serializable;

/**
 * Class Record implements read record from the dir.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Record implements Serializable
{

   /**
    * File path.
    * @var string
    */
   public $path;

   /**
    * Timestamp.
    * @var int
    */
   public $modifiedAt;

   /**
    * Hash unique value per record.
    * @var int
    */
   public $hash;

   /**
    * {@inheritdoc}
    */
   public function serialize()
   {
      return serialize(array($this->path, $this->modifiedAt, $this->hash));
   }

   /**
    * {@inheritdoc}
    */
   public function unserialize($serialized)
   {
      list($this->path, $this->modifiedAt, $this->hash) = unserialize($serialized);
   }

}