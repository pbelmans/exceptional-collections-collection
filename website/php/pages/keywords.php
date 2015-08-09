<?php

require_once("php/page.php");
require_once("php/keywords.php");

class KeywordsPage extends page {
  public function getMain() {
    $output = "";

    $keywords = getKeywords();
    // TODO maybe have pagination here?
    $output .= printKeywords($keywords);

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>

