<?php

require_once("php/page.php");
require_once("php/table.php");

class AboutPage extends page {
  public function getMain() {
    $output = "";

    $output .= "<h2>About</h2>";
    $output .= "...";

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>
