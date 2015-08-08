<?php

require_once("php/page.php");
require_once("php/table.php");

class IndexPage extends page {
  public function getMain() {
    global $articleFields;

    $sql = $this->db->prepare("SELECT " . $articleFields . " FROM articles");
    $articles = getArticles($sql);

    return printTable($articles);
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>
