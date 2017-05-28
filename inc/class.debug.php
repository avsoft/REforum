<?php
  class Debug {
    function __construct() {
      $this->sFlag = microtime(true);
    }
    function end() {
      $this->eFlag = microtime(true);
      $this->tFlag = $this->eFlag - $this->sFlag;
    }
  }
?>