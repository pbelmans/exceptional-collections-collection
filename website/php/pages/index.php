<?php

require_once("php/page.php");
require_once("php/table.php");

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
