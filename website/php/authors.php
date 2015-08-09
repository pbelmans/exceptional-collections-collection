<?php

/**
 * Data container class for authors in articles
 */
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
