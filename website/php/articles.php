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

/**
 * Data container class for articles
 */
class Article {
  public $id;

  public $title;
  public $year;
  public $authors = array();

  public $arXiv;
  public $MSC;
  public $zbMath;

  public function __construct($id, $title, $year) {
    $this->id = $id;
    $this->title = $title;
    $this->year = $year;
  }
}

// global variable
$authorArticleTable;

// create a global author-article lookup table
function createAuthorArticleTable() {
  global $database;
  global $authorArticleTable;

  // only do this once
  if (isset($authorArticleTable))
    return;

  $sql = $database->prepare("SELECT articles.id AS article, authors.id AS author, authors.firstname, authors.lastname FROM authors, articles, authorship WHERE articles.id = authorship.article AND authors.id = authorship.author");

  if ($sql->execute()) {
    $rows = $sql->fetchAll();

    foreach ($rows as $row) {
      if (!isset($authorArticleTable[$row["article"]]))
        $authorArticleTable[$row["article"]] = array();

      array_push($authorArticleTable[$row["article"]], new Author($row["author"], $row["firstname"], $row["lastname"]));
    }
  }
}

// associate authors to an article
function decorateWithAuthor($article) {
  global $authorArticleTable;

  createAuthorArticleTable();

  $article->authors = $authorArticleTable[$article->id];

  return $article;
}

?>
