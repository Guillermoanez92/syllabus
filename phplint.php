<?php
require_once __DIR__ . "/web/vendor/autoload.php";

use Overtrue\PHPLint\Linter;

$path = __DIR__ . "/web/app";
$exclude = array();
$extensions = ["php"];
$warnings = true;

$linter = new Linter($path, $exclude, $extensions, $warnings);

$errors = $linter->lint();

print_r($errors);
?>