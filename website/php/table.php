<?php

require_once("articles.php");

// main function
function printTable($articles) {
  $output = "";

  $output .= "<table class='table table-striped table-hover table-condensed'>";

  $output .= "<thead>";
  $output .= "<tr>";
  $output .= "<th id='authors'>author(s)</th>";
  $output .= "<th id='title'>title</th>";
  $output .= "<th id='links'>links</th>";
  $output .= "<th id='year'>year</th>";
  $output .= "</tr>";
  $output .= "</thead>";

  $output .= "<tbody>";
  foreach ($articles as $article) {
    $links = array("arXiv" => $article->arXiv["identifier"], "MSC" => $article->MSC["identifier"], "zbMath" => $article->zbMath["identifier"]);

    $output .= "<tr>";
    $output .= "<td>" . printAuthors($article->authors);
    $output .= "<td>" . $article->title;
    $output .= "<td class='links'>" . printLinks($links);
    $output .= "<td>" . printYear($article);
    $output .= "</tr>";
  }
  $output .= "</tbody>";

  $output .= "</table>";

  return $output;
}


// pretty print authors in the table
function printAuthors($authors) {
  $output = "<ol class='authors'>";

  foreach ($authors as $author)
    $output .= printAuthor($author);

  $output .= "</ol>";

  return $output;
}

// pretty print a single author in the table
function printAuthor($author) {
  $output = "";

  // not sure whether this is the best format
  $output .= "<li>" . $author->first . " <a href='" . href("authors/" . $author->id) . "'>" . $author->last . "</a>";

  return $output;
}

// pretty print links to arXiv, MSC and zbMath in the table
function printLinks($links) {
  $output = "";

  if (!empty($links["arXiv"]))
    $output .= "<span><a href='http://arxiv.org/abs/" . $links["arXiv"] . "'><img src='" . href("images/arxiv.ico") . "' height='16' alt='arXiv " . $links["arXiv"] . "'></a></span>";
  else
    $output .= "<span>&nbsp;</span>";

  if (!empty($links["MSC"]))
    $output .= "<span><a href='http://www.ams.org/mathscinet-getitem?mr=" . $links["MSC"] . "'><img src='" . href("images/msc.ico") . "' height='16' alt='MR" . $links["MSC"] . "'></a></span>";
  else
    $output .= "<span>&nbsp;</span>";

  if (!empty($links["zbMath"]))
    $output .= "<span><a href='https://zbmath.org/?q=an:" . $links["zbMath"] . "'><img src='" . href("images/zbmath.ico") . "' height='16' alt='Zbl" . $links["zbMath"] . "'></a></span>";
  else
    $output .= "<span>&nbsp;</span>";

  return $output;
}

// pretty print the year in the table
function printYear($article) {
  $output = "";

  // if the actual year is set we use this
  if (!empty($article->year)) {
    $output .= $article->year;

    // if there is moreover a preprint we also print its year of publication
    if (!empty($article->arXiv["identifier"])) {
      $output .= " (<span class='arxiv-year'>" . arXivIdentifierToYear($article->arXiv["identifier"]) . "</span>)";
    }
  }
  // if there is no year but there is an arXiv identifier we'll use this
  elseif (!empty($article->arXiv["identifier"])) {
    $output .= "&mdash; (<span class='arxiv-year'>" . arXivIdentifierToYear($article->arXiv["identifier"]) . "</span>)";
  }

  return $output;
}


/* helper functions */

// extract the year from the arXiv identifier
function arXivIdentifierToYear($identifier) {
  // based on http://arxiv.org/help/arxiv_identifier and http://arxiv.org/help/arxiv_identifier_for_services 

  // check for old resp. new scheme
  if (strpos($identifier, "/") !== false) {
    // old scheme
    $parts = explode("/", $identifier);

    if ($parts[1][0] == "0")
      return "20" . substr($parts[1], 0, 2);
    else
      return "19" . substr($parts[1], 0, 2);
  }
  else {
    // new scheme
    $parts = explode(".", $identifier);

    return "20" . substr($parts[0], 0, 2);
  }
}

?>
