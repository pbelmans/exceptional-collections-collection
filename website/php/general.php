<?php

$config = parse_ini_file("../config.ini");

function href($path) {
  global $config;

  return $config["prefix"] . $path;
}

?>
