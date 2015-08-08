<?php

require_once("php/page.php");
require_once("php/articles.php");

function getKeywords($article) {
  global $database;

  $keywords = array();

  $sql = $database->prepare("SELECT keywords.id, keywords.keyword, keywords.slug, keywords.description, (SELECT COUNT(*) FROM articlekeywords WHERE articlekeywords.keyword = keywords.id) AS occurrences FROM keywords, articlekeywords WHERE articlekeywords.article = :article AND articlekeywords.keyword = keywords.id");
  $sql->bindParam(":article", $article);

  if ($sql->execute()) {
    $rows = $sql->fetchAll();
    print_r($rows);

    foreach ($rows as $row)
      array_push($keywords, new Keyword($row["id"], $row["keyword"], $row["slug"], $row["description"], $row["occurrences"]));
  }

  return $keywords;
}

function printKeywords($keywords) {
  $output = "";

  $output .= "<div class='panel panel-info'>";
  $output .= "<div class='panel-heading'><h3 class='panel-title'>Keywords</h3></div>";
  $output .= "<div class='list-group'>";

  foreach ($keywords as $keyword)
    $output .= "<a href='" . href("keyword/" . $keyword->slug) . "' class='list-group-item list-group-condensed'><span class='badge'>" . $keyword->occurrences . "</span>" . $keyword->keyword . "</a>";

  $output .= "</ul>";
  $output .= "</div>";

  return $output;
}


class ArticlePage extends page {
  private $id;

  public function __construct($database, $article) {
    $this->db = $database;
    $this->id = $article;
  }

  public function getMain() {
    global $articleFields;

    $sql = $this->db->prepare("SELECT " . $articleFields . " FROM articles WHERE articles.id = :article");
    $sql->bindParam(":article", $this->id);
    $article = getArticles($sql)[0];

    $output = "";

    $output .= "<h2>" . $article->title . "</h2>";

    $output .= printAuthors($article->authors);
    $output .= printKeywords(getKeywords($article->id));

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}

