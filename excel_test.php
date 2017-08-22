<?php

require __DIR__ . '/vendor/autoload.php';

$objPHPExcel = PHPExcel_IOFactory::load('xlsx/rasp.xlsx');

$objWorksheet = $objPHPExcel->getActiveSheet();

$highestRow = $objWorksheet->getHighestDataRow();

$startReading = 2;

for ($i = $startReading; $i <= $highestRow; $i++) {
	$date = $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getValue();
	$date = PHPExcel_Shared_Date::ExcelToPHP($date);
	if (time() >= strtotime('-8 hours -1 minutes', $date) && time() <= strtotime('-7 hours -59 minutes', $date)) {
		$timing[$i]['reminder']['pass'] = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
		$timing[$i]['reminder']['text'] = $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getValue();
	}
	if (time() >= strtotime('-3 hours -16 minutes', $date) && time() <= strtotime('-3 hours -14 minutes', $date)) {
		$timing[$i]['event']['pass'] = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
		$timing[$i]['event']['text'] = $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getValue();
	}
}

if(!is_null($timing)) {
	foreach ($timing as $event) {
		var_dump($event);
	}
}