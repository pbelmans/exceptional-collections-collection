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

function getKeywords() {
  global $database;

  $keywords = array();

  $sql = $database->prepare("SELECT keywords.id, keywords.keyword, keywords.slug, keywords.description, (SELECT COUNT(*) FROM articlekeywords WHERE articlekeywords.keyword = keywords.id) AS occurrences FROM keywords ORDER BY occurrences DESC");

  if ($sql->execute()) {
    $rows = $sql->fetchAll();

    foreach ($rows as $row)
      array_push($keywords, new Keyword($row["id"], $row["keyword"], $row["slug"], $row["description"], $row["occurrences"]));
  }

  return $keywords;
}

// get keywords that appear in conjunction with this keyword
function getRelatedKeywords($id) {
  global $database;

  $keywords = array();

  $sql = $database->prepare("SELECT DISTINCT keywords.id, keywords.keyword, keywords.slug, keywords.description, (SELECT COUNT(*) FROM articlekeywords WHERE articlekeywords.keyword = keywords.id) AS occurrences FROM keywords, articlekeywords WHERE articlekeywords.article IN (SELECT articlekeywords.article FROM articlekeywords WHERE articlekeywords.keyword = :id) AND NOT articlekeywords.keyword = :id ORDER BY occurrences DESC");
  $sql->bindParam(":id", $id);

  if ($sql->execute()) {
    $rows = $sql->fetchAll();

    foreach ($rows as $row)
      array_push($keywords, new Keyword($row["id"], $row["keyword"], $row["slug"], $row["description"], $row["occurrences"]));
  }

  return $keywords;
}

function printKeywords($keywords) {
  $output = "";

  $output .= "<div class='list-group'>";

  foreach ($keywords as $keyword)
    $output .= "<a href='" . href("keywords/" . $keyword->slug) . "' class='list-group-item list-group-condensed'><span class='badge'>" . $keyword->occurrences . "</span>" . $keyword->keyword . "</a>";

  $output .= "</div>";

  return $output;
}


?>
