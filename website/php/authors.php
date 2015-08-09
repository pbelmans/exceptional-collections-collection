<?php

/**
 * Data container class for authors in articles
 */
class Author {
  public $id;

  public $first;
  public $last;

  // -1 indicates not set (not very nice, I know...)
  public $coauthors = -1;
  public $articles = -1;

  public function __construct($id, $first, $last) {
    $this->id = $id;
    $this->first = $first;
    $this->last = $last;
  }
}

function getAuthors() {
  global $database;

  $authors = array();

  $sql = $database->prepare("SELECT id, firstname, lastname, (SELECT COUNT(authorship.article) FROM authorship WHERE authorship.author = authors.id) AS articles, (SELECT DISTINCT COUNT(authorship.author) FROM authorship WHERE authorship.article IN (SELECT authorship.article FROM authorship WHERE authorship.author = authors.id) AND NOT authorship.author = authors.id) AS coauthors FROM authors");

  if ($sql->execute()) {
    $rows = $sql->fetchAll();

    foreach ($rows as $row) {
      $author = new Author($row["id"], $row["firstname"], $row["lastname"]);
      $author->coauthors = $row["coauthors"];
      $author->articles = $row["articles"];

      array_push($authors, $author);
    }
  }

  return $authors;
}

function getAuthor($id) {
  global $database;

  $sql = $database->prepare("SELECT authors.id, authors.firstname, authors.lastname FROM authors WHERE authors.id = :author");
  $sql->bindParam(":author", $id);

  if ($sql->execute()) {
    $author = $sql->fetch();

    return new Author($author["id"], $author["firstname"], $author["lastname"]);
  }
}

?>
