<?php
require 'vendor/PhpSpreadsheet';

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load("tpl.xlsx");
?>