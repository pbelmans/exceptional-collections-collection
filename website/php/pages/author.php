<?php

require_once("php/page.php");
require_once("php/table.php");


class AuthorPage extends page {
  private $id;

  public function __construct($database, $author) {
    $this->db = $database;
    $this->id = $author;
  }

  public function getMain() {
    global $articleFields;

    $sql = $this->db->prepare("SELECT " . $articleFields . " FROM articles, authorship WHERE authorship.article = articles.id AND authorship.author = :author");
    $sql->bindParam(":author", $this->id);
    $articles = getArticles($sql);

    return printTable($articles);
  }

  public function getTitle() {
    return "barfoo";
  }
}
