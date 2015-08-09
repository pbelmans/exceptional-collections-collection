<?php

/**
 * Data container for keywords
 */
class Keyword {
  public $id;

  public $keyword;
  public $slug;
  public $description;
  public $occurrences;

  public function __construct($id, $keyword, $slug, $description, $occurrences) {
    $this->id = $id;
    $this->keyword = $keyword;
    $this->slug = $slug;
    $this->description = $description;
    $this->occurrences = $occurrences;
  }
}

function keywordExists($slug) {
  // TODO implement this
  return true;
}

function getKeywordFromSlug($slug) {
  global $database;

  $sql = $database->prepare("SELECT id, keyword, slug, description, (SELECT COUNT(*) FROM keywords, articlekeywords WHERE keywords.id = articlekeywords.keyword AND keywords.slug = :slug) AS occurrences FROM keywords WHERE keywords.slug = :slug");
  $sql->bindParam(":slug", $slug);

  if ($sql->execute()) {
    $keyword = $sql->fetch();

    return new Keyword($keyword["id"], $keyword["keyword"], $keyword["slug"], $keyword["description"], $keyword["occurrences"]);
  }
}

?>
