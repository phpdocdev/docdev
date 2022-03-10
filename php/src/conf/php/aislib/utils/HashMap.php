<?php

class HashMap {

  var $keys;
  var $values;

  function HashMap() {
    $this->keys   = array();
    $this->values = array();
  }

  function add($key, $value) {
    $this->keys[] = $key;
    $this->values[] = $value;
  }

  function get($key) {
    if (in_array($key, $this->keys)) {
      for ($i = 0; $i < count($this->keys); $i++) {
        if ($this->keys[$i] == $key) {
          return $this->values[$i];
        }
      }
    }
    return ;
  }

}


?>