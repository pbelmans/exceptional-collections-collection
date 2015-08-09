<?php

require_once("php/page.php");
require_once("php/keywords.php");

class KeywordsPage extends page {
  public function getMain() {
    $output = "";

    $output .= "<div id='keywords' class='panel panel-default'>";
    $output .= "<div class='panel-heading'><h3 class='panel-title'>Keywords</h3></div>";
    $keywords = getKeywords();
    // TODO maybe have pagination here?
    $output .= printKeywords($keywords);
    $output .= "</div>";
    $output .= "</div>";

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>
