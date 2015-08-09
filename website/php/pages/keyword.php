<?php

require_once("php/page.php");
require_once("php/keywords.php");

class KeywordPage extends page {
  private $keyword;

  public function __construct($database, $keyword) {
    $this->db = $database;
    $this->keyword = $keyword;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>" . $this->keyword->keyword . "</h2>";
    $output .= "<blockquote>" . $this->keyword->description . "</blockquote>";

    global $articleFields;

    $sql = $this->db->prepare("SELECT " . $articleFields . " FROM articles, articlekeywords WHERE articles.id = articlekeywords.article AND articlekeywords.keyword = :keyword");
    $sql->bindParam(":keyword", $this->keyword->id);
    $articles = getArticles($sql);

    $output .= "<div class='panel panel-default'>";
    $output .= "<div class='panel-heading'><h3 class='panel-title'>Articles with this keyword</h3></div>";
    $output .= "<div class='panel-body'>"; // TODO make this collapsible (and collapsed by default?)
    $output .= printTable($articles);
    $output .= "</div>";
    $output .= "</div>";

    // TODO maybe a "related keywords" panel?

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>
