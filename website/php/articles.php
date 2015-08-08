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

// feed this a query that gives a bunch of articles and it'll give you the correct data format for the table printer
function getArticles($sql) {
  global $database;

  $articles = array();

  // prepare the articles
  if ($sql->execute()) {
    $articleRows = $sql->fetchAll();

    foreach ($articleRows as $articleRow) {
      // create new article object
      $article = new Article($articleRow["id"], $articleRow["title"], $articleRow["year"]);
      $article = decorateWithAuthor($article);

      array_push($articles, $article);

      // associate arXiv information to articles
      // TODO this should be in the first query selecting all articles via some JOIN magic?
      $sql = $database->prepare("SELECT arxiv.identifier, arxiv.category FROM arxiv, articles WHERE articles.id = arxiv.article AND articles.id = :article");
      $sql->bindParam(":article", $article->id);

      if ($sql->execute()) {
        $arXivRows = $sql->fetchAll(); // TODO this returns 0 or 1, improve this...

        foreach ($arXivRows as $arXivRow) {
          $article->arXiv = $arXivRow;
        }
      }
    }
  }

  return $articles;
}



?>
