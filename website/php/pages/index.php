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

  public $arXiv;
  public $MSC;
  public $zbMath;

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

      // associate authors to articles
      $sql = $database->prepare("SELECT authors.id, authors.firstname, authors.lastname FROM authors, articles, authorship WHERE articles.id = authorship.article AND authors.id = authorship.author AND articles.id = :article");
      $sql->bindParam(":article", $article->id);

      if ($sql->execute()) {
        $authorRows = $sql->fetchAll();

        foreach ($authorRows as $authorRow) {
          array_push($article->authors, new Author($authorRow["id"], $authorRow["firstname"], $authorRow["lastname"]));
        }
      }

      // associate arXiv information to articles
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

function printAuthors($authors) {
  $output = "";

  for ($i = 0; $i < count($authors); $i++) {
    $output .= $authors[$i]->first . " " . $authors[$i]->last;

    if ($i != count($authors) - 1)
      $output .= ", ";
  }

  return $output;
}

function printLinks($links) {
  $output = "";

  if (!empty($links["arXiv"]))
    $output .= "<span><a href='http://arxiv.org/abs/" . $links["arXiv"] . "'><img src='images/arxiv.ico' height='16'></a></span>";
  else
    $output .= "<span>&nbsp;</span>";

  if (!empty($links["MSC"]))
    $output .= "<span><a href='http://www.ams.org/mathscinet-getitem?mr=" . $links["MSC"] . "'><img src='images/msc.ico' height='16'></a></span>";
  else
    $output .= "<span>&nbsp;</span>";

  if (!empty($links["zbMath"]))
    $output .= "<span><a href='https://zbmath.org/?q=an:" . $links["zbMath"] . "'><img src='images/zbmath.ico' height='16'></a></span>";
  else
    $output .= "<span>&nbsp;</span>";

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
    $links = array("arXiv" => $article->arXiv["identifier"], "MSC" => $article->MSC["identifier"], "zbMath" => $article->zbMath["identifier"]);

    $output .= "<tr>";
    $output .= "<td>" . printAuthors($article->authors);
    $output .= "<td>" . $article->title;
    $output .= "<td class='links'>" . printLinks($links);
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
