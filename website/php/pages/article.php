<?php

require_once("php/page.php");
require_once("php/articles.php");

function getKeywordsForArticle($article) {
  global $database;

  $keywords = array();

  $sql = $database->prepare("SELECT keywords.id, keywords.keyword, keywords.slug, keywords.description, (SELECT COUNT(*) FROM articlekeywords WHERE articlekeywords.keyword = keywords.id) AS occurrences FROM keywords, articlekeywords WHERE articlekeywords.article = :article AND articlekeywords.keyword = keywords.id");
  $sql->bindParam(":article", $article);

  if ($sql->execute()) {
    $rows = $sql->fetchAll();

    foreach ($rows as $row)
      array_push($keywords, new Keyword($row["id"], $row["keyword"], $row["slug"], $row["description"], $row["occurrences"]));
  }

  return $keywords;
}

function printKeywords($keywords) {
  $output = "";

  $output .= "<div class='col-md-8'>";
  $output .= "<div id='keywords' class='panel panel-default'>";
  $output .= "<div class='panel-heading'><h3 class='panel-title'>Keywords</h3></div>";
  $output .= "<div class='list-group'>";

  foreach ($keywords as $keyword)
    $output .= "<a href='" . href("keywords/" . $keyword->slug) . "' class='list-group-item list-group-condensed'><span class='badge'>" . $keyword->occurrences . "</span>" . $keyword->keyword . "</a>";

  $output .= "</div>";
  $output .= "</div>";
  $output .= "</div>";

  return $output;
}

function arXivLinkFull($arxiv) {
  return "<a class='list-group-item' href='http://arxiv.org/abs/" . $arxiv["identifier"] . "'><img src='" . href("images/arxiv.ico") . "' height='16' alt='arXiv " . $arxiv["identifier"] . "'> arXiv:" . $arxiv["identifier"] . "</a>";
}

function MSCLinkFull($msc) {
  return "<a class='list-group-item' href='http://www.ams.org/mathscinet-getitem?mr=" . $msc["identifier"] . "'><img src='" . href("images/msc.ico") . "' height='16' alt='MR" . $msc["identifier"] . "'> " . $msc["identifier"] . "</a>";
}

function zbMathLinkFull($zbmath) {
  return "<a class='list-group-item' href='https://zbmath.org/?q=an:" . $zbmath["zbMath"] . "'><img src='" . href("images/zbmath.ico") . "' height='16' alt='Zbl" . $zbmath["zbMath"] . "'> " . $zbmath["identifier"] . "</a>";
}

function printLinksPanel($article) {
  $output = "";

  $output .= "<div class='col-md-4'>";
  $output .= "<div id='links-list' class='panel panel-default'>";
  $output .= "<div class='panel-heading'><h3 class='panel-title'>Links</h3></div>";
  $output .= "<div class='list-group'>";
  if (!empty($article->arXiv["identifier"]))
    $output .= arXivLinkFull($article->arXiv);
  if (!empty($article->MSC["identifier"]))
    $output .= MSCLinkFull($article->MSC);
  if (!empty($article->zbMath["identifier"]))
    $output .= zbMathLinkFull($article->zbMath);
  if (!empty($article->arXiv["identifier"]) and !empty($article->MSC["identifier"]) and !empty($article->zbMath["identifier"]))
    $output .= "<li class='list-group-item'><em>No links known.</em>";
  $output .= "</div>";
  $output .= "</div>";
  $output .= "</div>";

  return $output;
}


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
    $articles = getArticles($sql);
    $article = $articles[0];

    $output = "";

    $output .= "<h2>" . $article->title . "</h2>";

    $output .= "<div id='authors'>";
    $output .= printAuthors($article->authors);
    $output .= "</div>";

    $output .= "<div class='row'>";
    $output .= printLinksPanel($article);
    $output .= printKeywords(getKeywordsForArticle($article->id));
    $output .= "</div>";

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}

