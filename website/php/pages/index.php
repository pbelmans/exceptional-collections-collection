<?php

require_once("php/page.php");
require_once("php/table.php");

class IndexPage extends page {
  public function getMain() {
    $sql = $this->db->prepare("SELECT articles.id, articles.title, articles.year FROM articles");

    $articles = getArticles($sql);

    return printTable($articles);
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>
