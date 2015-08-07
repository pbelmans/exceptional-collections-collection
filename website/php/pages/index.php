<?php

require_once("php/page.php");

class Author {
  public $id;

  public $first;
  public $last;

  public function __construct($id, $first, $last) {
    $this->id = $id;
    $this->first = $first;
    $this->last = $last;
  }
}

class Article {
  public $id;

  public $title;
  public $year;
  public $authors = array();

  public function __construct($id, $title, $year) {
    $this->id = $id;
    $this->title = $title;
    $this->year = $year;
  }
}

function getArticles() {
  global $database;

  $articles = array();

  // prepare the articles
  $sql = $database->prepare("SELECT articles.id, articles.title, articles.year FROM articles");

  if ($sql->execute()) {
    $articleRows = $sql->fetchAll();

    foreach ($articleRows as $articleRow) {
      // create new article object
      $article = new Article($articleRow["id"], $articleRow["title"], $articleRow["year"]);
      array_push($articles, $article);

      // link authors to articles
      $sql = $database->prepare("SELECT authors.id, authors.firstname, authors.lastname FROM authors, articles, authorship WHERE articles.id = authorship.article AND authors.id = authorship.author AND articles.id = :article");
      $sql->bindParam(":article", $article->id);

      if ($sql->execute()) {
        $authorRows = $sql->fetchAll();

        foreach ($authorRows as $authorRow) {
          array_push($article->authors, new Author($authorRow["id"], $authorRow["firstname"], $authorRow["lastname"]));
        }
      }
    }
  }

  return $articles;
}

function printAuthors($authors) {
  $output = "";

  for ($i = 0; $i < count($authors); $i++) {
    $output .= $authors[$i]->first . " " . $authors[$i]->last;

    if ($i != count($authors) - 1)
      $output .= ", ";
  }

  return $output;
}

function printTable($articles) {
  $output = "";

  $output .= "<table>";
  $output .= "<tr>";
  $output .= "<th id='authors'>author(s)</th>";
  $output .= "<th id='title'>title</th>";
  $output .= "<th id='links'>links</th>"; // TODO put favicons here
  $output .= "<th id='year'>year</th>";
  $output .= "</tr>";

  foreach ($articles as $article) {
    $output .= "<tr>";
    $output .= "<td>" . printAuthors($article->authors);
    $output .= "<td>" . $article->title;
    $output .= "<td>";
    $output .= "<td>" . $article->year;
    $output .= "</tr>";
  }
  $output .= "</table>";

  return $output;
}

class IndexPage extends page {
  public function getMain() {
    $articles = getArticles();
    return printTable($articles);
  }

  public function getTitle() {
    return "barfoo";
  }
}

?>
