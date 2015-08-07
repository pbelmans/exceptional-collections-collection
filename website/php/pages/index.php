<?php

require_once("php/page.php");

class IndexPage extends page {
  public function getMain() {
    return "foobar";
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>
