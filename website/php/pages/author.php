<?php

require_once("php/page.php");
require_once("php/authors.php");
require_once("php/table.php");


class AuthorPage extends page {
  private $id;

  public function __construct($database, $author) {
    $this->db = $database;
    $this->id = $author;
  }

  public function getMain() {
    $output = "";

    $author = getAuthor($this->id);
    $output .= "<h2>" . $author->first . " " . $author->last . "</h2>";

    global $articleFields;

    $sql = $this->db->prepare("SELECT " . $articleFields . " FROM articles, authorship WHERE authorship.article = articles.id AND authorship.author = :author");
    $sql->bindParam(":author", $this->id);
    $output .= printTable(getArticles($sql));

    // TODO overview of co-authors

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}
