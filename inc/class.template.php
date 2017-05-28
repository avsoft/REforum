<?php
  class Template {
    var $file;
    var $macros = array();
    function __construct($t,$s) {
      $dir = "themes/{$t}/{$s}.tpl";
      if(!file_exists($dir)) {
        die("Missing template file.");
      }
      $this->file = $dir;
    }
    function set($k,$v) {
      $this->macros[$k] = $v;
    }
    function output() {
      $html = file_get_contents($this->file);
      foreach($this->macros as $k => $v) {
        $html = str_replace("{{$k}}",$v,$html);
      }
      return $html;
    }
  }