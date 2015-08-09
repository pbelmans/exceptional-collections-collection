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
    $articles = getArticles($sql);

    $output .= "<div class='panel panel-default'>";
    $output .= "<div class='panel-heading'><h3 class='panel-title'>Articles by this author</h3></div>";
    $output .= "<div class='panel-body'>"; // TODO make this collapsible (and collapsed by default?)
    $output .= printTable($articles);
    $output .= "</div>";
    $output .= "</div>";
    $output .= "</div>";

    // TODO overview of co-authors

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}
