<?php
include_once("___auth.data.php");

require_once("vendor/MysqliDb.php");
require_once("vendor/dbObject.php");

// db instance
$db = new Mysqlidb('localhost', $db_user_name, $db_password, $db_scheme);
// enable class autoloading
dbObject::autoload("models");

function downloadData() {
	global $ftp_server, $ftp_port, $ftp_user, $ftp_password, $ftp_path;

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

	$xls_data = array();
	ftp_chdir($conn_id, $ftp_path);
	$contents = ftp_nlist($conn_id, ".");
	if (is_array($contents)) {
		for ($i=0; $i<count($contents); $i++) {
			$fname = $contents[$i];
			if (preg_match("/^Save\\d+\.CSV$/", $fname)) {
				if (ftp_get($conn_id, "./tmp/$fname", $fname, FTP_BINARY)) {
					$handle = fopen("./tmp/$fname", "r");
					$csv = fread($handle, filesize("./tmp/$fname"));
					if ($csv) {
						$data = split(",", $csv);
						if (is_array($data)) {
							$dt = str_replace(" ", "-", substr(trim($data[1]), 0, 4)." ".$data[0]);
							$herd = intval($data[2]);
							if (!is_array($xls_data[$dt])) {
								$xls_data[$dt] = array();
							}
							if (!is_array($xls_data[$dt][$herd])) {
								$xls_data[$dt][$herd] = array();
							}
							if (!is_array($xls_data[$dt][$herd]["m"])) {
								$xls_data[$dt][$herd]["m"] = array();
							}
							if (!is_array($xls_data[$dt][$herd]["f"])) {
								$xls_data[$dt][$herd]["f"] = array();
							}
							$dat = array_slice($data, 7);
							for ($j=0; $j<48; $j++) {
								if ($j%2) {
									array_push($xls_data[$dt][$herd]["m"], intval($dat[$j]));
								} else {
									array_push($xls_data[$dt][$herd]["f"], intval($dat[$j]));
								}
							}
							$female = xls_data::where("DATE", $dt)->where("HERD", $herd)->where("GENDER", 0)->getOne();
							$male = xls_data::where("DATE", $dt)->where("HERD", $herd)->where("GENDER", 1)->getOne();
							if (!$female instanceof xls_data) {
								$female = new xls_data;
								$female->DATE = $dt;
								$female->HERD = $herd;
								$female->GENDER = 0;
							}
							if (!$male instanceof xls_data) {
								$male = new xls_data;
								$male->DATE = $dt;
								$male->HERD = $herd;
								$male->GENDER = 1;
							}
							$female->P1 = $xls_data[$dt][$herd]["f"][0];
							$female->P2 = $xls_data[$dt][$herd]["f"][1];
							$female->P3 = $xls_data[$dt][$herd]["f"][2];
							$female->P4 = $xls_data[$dt][$herd]["f"][3];
							$female->P5 = $xls_data[$dt][$herd]["f"][4];
							$female->P6 = $xls_data[$dt][$herd]["f"][5];
							$female->P7 = $xls_data[$dt][$herd]["f"][6];
							$female->P8 = $xls_data[$dt][$herd]["f"][7];
							$female->P9 = $xls_data[$dt][$herd]["f"][8];
							$female->P10 = $xls_data[$dt][$herd]["f"][9];
							$female->P11 = $xls_data[$dt][$herd]["f"][10];
							$female->P12 = $xls_data[$dt][$herd]["f"][11];
							$female->P13 = $xls_data[$dt][$herd]["f"][12];
							$female->P14 = $xls_data[$dt][$herd]["f"][13];
							$female->P15 = $xls_data[$dt][$herd]["f"][14];
							$female->P16 = $xls_data[$dt][$herd]["f"][15];
							$female->P17 = $xls_data[$dt][$herd]["f"][16];
							$female->P18 = $xls_data[$dt][$herd]["f"][17];
							$female->P19 = $xls_data[$dt][$herd]["f"][18];
							$female->P20 = $xls_data[$dt][$herd]["f"][19];
							$female->P21 = $xls_data[$dt][$herd]["f"][20];
							$female->P22 = $xls_data[$dt][$herd]["f"][21];
							$female->P23 = $xls_data[$dt][$herd]["f"][22];
							$female->P24 = $xls_data[$dt][$herd]["f"][23];
	
							$male->P1 = $xls_data[$dt][$herd]["m"][0];
							$male->P2 = $xls_data[$dt][$herd]["m"][1];
							$male->P3 = $xls_data[$dt][$herd]["m"][2];
							$male->P4 = $xls_data[$dt][$herd]["m"][3];
							$male->P5 = $xls_data[$dt][$herd]["m"][4];
							$male->P6 = $xls_data[$dt][$herd]["m"][5];
							$male->P7 = $xls_data[$dt][$herd]["m"][6];
							$male->P8 = $xls_data[$dt][$herd]["m"][7];
							$male->P9 = $xls_data[$dt][$herd]["m"][8];
							$male->P10 = $xls_data[$dt][$herd]["m"][9];
							$male->P11 = $xls_data[$dt][$herd]["m"][10];
							$male->P12 = $xls_data[$dt][$herd]["m"][11];
							$male->P13 = $xls_data[$dt][$herd]["m"][12];
							$male->P14 = $xls_data[$dt][$herd]["m"][13];
							$male->P15 = $xls_data[$dt][$herd]["m"][14];
							$male->P16 = $xls_data[$dt][$herd]["m"][15];
							$male->P17 = $xls_data[$dt][$herd]["m"][16];
							$male->P18 = $xls_data[$dt][$herd]["m"][17];
							$male->P19 = $xls_data[$dt][$herd]["m"][18];
							$male->P20 = $xls_data[$dt][$herd]["m"][19];
							$male->P21 = $xls_data[$dt][$herd]["m"][20];
							$male->P22 = $xls_data[$dt][$herd]["m"][21];
							$male->P23 = $xls_data[$dt][$herd]["m"][22];
							$male->P24 = $xls_data[$dt][$herd]["m"][23];
	                         
	                        $rc = $female->save();
	                        if (!$rc) {
	                            print_r($female->errors);
	                            print_r($db->getLastError());
	                        }
	                        $rc = $male->save();
	                        if (!$rc) {
	                            print_r($female->errors);
	                            print_r($db->getLastError());
	                        }
							
						}
					}
					fclose($handle);
				}
			}
		}
	}
	ftp_close($conn_id); 

	$ret->dates = array_keys($xls_data);
	return $ret;
}

$rc = downloadData();
if ($rc->error) {
	echo $rc->error;
} else {
	echo "OK";
}
?>