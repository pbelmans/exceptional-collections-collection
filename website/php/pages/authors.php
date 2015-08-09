<?php

require_once("php/page.php");
require_once("php/authors.php");
require_once("php/table.php");

function printAuthorsTable($authors) {
  $output = "";

  $output .= "<table id='authors' class='table table-striped table-hover table-condensed'>";

  $output .= "<thead>";
  $output .= "<tr>";
  $output .= "<th>name</th>";
  $output .= "<th># articles</th>";
  $output .= "<th># coauthors</th>";
  $output .= "</tr>";
  $output .= "</thead>";

  $output .= "<tbody>";
  foreach ($authors as $author) {
    $output .= "<tr>";
    $output .= "<td data-order='" . $author->last . "'>" . printAuthor($author);
    $output .= "<td>" . $author->articles;
    $output .= "<td>" . $author->coauthors;
    $output .= "</tr>";
  }
  $output .= "</tbody>";

  $output .= "</table>";

  return $output;
}

class AuthorsPage extends page {
  public function getMain() {
    $output = "";

    $output .= "<div id='keywords' class='panel panel-default'>";
    $output .= "<div class='panel-heading'><h3 class='panel-title'>Authors</h3></div>";
    $output .= "<div class='panel-body'>";
    $authors = getAuthors();
    $output .= printAuthorsTable($authors);
    $output .= "</div>";
    $output .= "</div>";
    $output .= "</div>";

    return $output;
  }

  public function getTitle() {
    return "barfoo";
  }
}
