<?php

require_once("php/page.php");


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

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}

