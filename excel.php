<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/index.php';

$objPHPExcel = PHPExcel_IOFactory::load(__DIR__ . '/xlsx/rasp.xlsx');

$objWorksheet = $objPHPExcel->getActiveSheet();

$highestRow = $objWorksheet->getHighestDataRow();

$startReading = 2;

for ($i = $startReading; $i <= $highestRow; $i++) {
  $date = $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getValue();
  $date = PHPExcel_Shared_Date::ExcelToPHP($date);
  if (time() >= strtotime('-8 hours -1 minutes', $date) && time() <= strtotime('-7 hours -59 minutes', $date)) {
    $timing[$i]['reminder']['pass'] = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
    $timing[$i]['reminder']['date'] = date('d.m.y G:i', strtotime('-3 hours', $date));
    $timing[$i]['reminder']['text'] = $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getValue();
  }
  if (time() >= strtotime('-3 hours -16 minutes', $date) && time() <= strtotime('-3 hours -14 minutes', $date)) {
    $timing[$i]['event']['pass'] = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
    $timing[$i]['event']['date'] = date('d.m.y G:i', strtotime('-3 hours', $date));
    $timing[$i]['event']['text'] = $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getValue();
  }
}

$IDandPass = file_get_contents(__DIR__ . '/passlist.json');
$IDandPass = json_decode($IDandPass, true);

if (isset($timing)) {
  foreach ($timing as $time) {
    if (isset($time['reminder']['text'])) {
      foreach ($IDandPass as $user) {
        if ($time['reminder']['pass'] === array_values($user)[0]) {
          $lessonManager->sendMessage($time['reminder']['text'], array_keys($user)[0]);
          $lessonManager->whatWasSent(array_keys($user)[0], $time['reminder']['text'], $time['reminder']['date']);
        }
      }
    }

    if (isset($time['event']['text'])) {
      foreach ($IDandPass as $user) {
        if ($time['event']['pass'] === array_values($user)[0]) {
          $lessonManager->sendMessage($time['event']['text'], array_keys($user)[0]);
          $lessonManager->whatWasSent(array_keys($user)[0], $time['event']['text'], $time['event']['date']);
        }
      }
    }
  }
}
