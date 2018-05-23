<?php
include_once("___auth.data.php");

require_once("vendor/MysqliDb.php");
require_once("vendor/dbObject.php");

// db instance
$db = new Mysqlidb('localhost', $db_user_name, $db_password, $db_scheme);
// enable class autoloading
dbObject::autoload("models");

require_once('vendor1/autoload.php'); 

function regenerateXls() {
	global $ftp_server, $ftp_port, $ftp_user, $ftp_password, $ftp_path, $ftp_reports_path;

	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

	$ret = (object)array();
	$conn_id = ftp_connect($ftp_server, $ftp_port); 
	if (!$conn_id) {
		$ret->error = "CANT_CONNECT";
		return $ret;
	}

	if (!@ftp_login($conn_id, $ftp_user, $ftp_password)) {
		$ret->error = "CANT_LOGIN";
		return $ret;
	}

	ftp_chdir($conn_id, $ftp_path);

	$dates = xls_data::groupBy("DATE")->orderBy("DATE", "desc")->get();
	if ($dates) {
		foreach ($dates as $f) {
			$fname = generate_xls($reader, $f->DATE);

			if ($fname) {
				if (!ftp_put($conn_id, $ftp_reports_path."/".$fname.".xlsx", "reports/".$fname.".xlsx", FTP_BINARY)) {
				}
			}
		}
	}
}

function generate_xls($reader, $date) {
	$female = xls_data::where("DATE", $date)->where("GENDER", 0)->orderBy("HERD", "asc")->get();
	$male = xls_data::where("DATE", $date)->where("GENDER", 1)->orderBy("HERD", "asc")->get();
    if ($female && $male) {
		$spreadsheet = $reader->load('tpl.xlsx');
		$sheet = $spreadsheet->getActiveSheet();

	    foreach ($female as $f) {
	    	$herd = $f->HERD;
	    	$row = $herd + intval(($herd-1)/5) + 3;

			$sheet->setCellValue('C'.$row, $f->P1);
			$sheet->setCellValue('D'.$row, $f->P2);
			$sheet->setCellValue('E'.$row, $f->P3);
			$sheet->setCellValue('F'.$row, $f->P4);
			$sheet->setCellValue('G'.$row, $f->P5);
			$sheet->setCellValue('H'.$row, $f->P6);
			$sheet->setCellValue('I'.$row, $f->P7);
			$sheet->setCellValue('J'.$row, $f->P8);
			$sheet->setCellValue('K'.$row, $f->P9);
			$sheet->setCellValue('L'.$row, $f->P10);
			$sheet->setCellValue('M'.$row, $f->P11);
			$sheet->setCellValue('N'.$row, $f->P12);
			$sheet->setCellValue('O'.$row, $f->P13);
			$sheet->setCellValue('P'.$row, $f->P14);
			$sheet->setCellValue('Q'.$row, $f->P15);
			$sheet->setCellValue('R'.$row, $f->P16);
			$sheet->setCellValue('S'.$row, $f->P17);
			$sheet->setCellValue('T'.$row, $f->P18);
			$sheet->setCellValue('U'.$row, $f->P19);
			$sheet->setCellValue('V'.$row, $f->P20);
			$sheet->setCellValue('W'.$row, $f->P21);
			$sheet->setCellValue('X'.$row, $f->P22);
			$sheet->setCellValue('Y'.$row, $f->P23);
			$sheet->setCellValue('Z'.$row, $f->P24);
	    }

	    foreach ($male as $f) {
	    	$herd = $f->HERD;
	    	$row = $herd + intval(($herd-1)/5) + 26;

			$sheet->setCellValue('C'.$row, $f->P1);
			$sheet->setCellValue('D'.$row, $f->P2);
			$sheet->setCellValue('E'.$row, $f->P3);
			$sheet->setCellValue('F'.$row, $f->P4);
			$sheet->setCellValue('G'.$row, $f->P5);
			$sheet->setCellValue('H'.$row, $f->P6);
			$sheet->setCellValue('I'.$row, $f->P7);
			$sheet->setCellValue('J'.$row, $f->P8);
			$sheet->setCellValue('K'.$row, $f->P9);
			$sheet->setCellValue('L'.$row, $f->P10);
			$sheet->setCellValue('M'.$row, $f->P11);
			$sheet->setCellValue('N'.$row, $f->P12);
			$sheet->setCellValue('O'.$row, $f->P13);
			$sheet->setCellValue('P'.$row, $f->P14);
			$sheet->setCellValue('Q'.$row, $f->P15);
			$sheet->setCellValue('R'.$row, $f->P16);
			$sheet->setCellValue('S'.$row, $f->P17);
			$sheet->setCellValue('T'.$row, $f->P18);
			$sheet->setCellValue('U'.$row, $f->P19);
			$sheet->setCellValue('V'.$row, $f->P20);
			$sheet->setCellValue('W'.$row, $f->P21);
			$sheet->setCellValue('X'.$row, $f->P22);
			$sheet->setCellValue('Y'.$row, $f->P23);
			$sheet->setCellValue('Z'.$row, $f->P24);
	    }

	    $fname = "Result ".$date;
		$writerXLS = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writerXLS->save("reports/".$fname.".xlsx");

		$writerHTML = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
		$writerHTML->save("reports/".$fname.".htm");

		return $fname;
   }
}

$rc = regenerateXls();
if ($rc->error) {
	echo $rc->error;
} else {
	echo "OK";
}

?>