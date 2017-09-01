<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/index.php';

$objPHPExcel = PHPExcel_IOFactory::load(__DIR__ . '/xlsx/rasp.xlsx');

$objWorksheet = $objPHPExcel->getActiveSheet();

$highestRow = $objWorksheet->getHighestDataRow();

$startReading = 2;

for ($i = $startReading; $i <= $highestRow; $i++) {
  $pass = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
  $date = $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getValue();
  $date = PHPExcel_Shared_Date::ExcelToPHP($date);
  if (!is_null($pass)) {
    $row[$i]['pass'] = $pass;
    $row[$i]['date'] = strtotime('-3 hours', $date);
    $row[$i]['event'] = $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getValue();
    $row[$i]['reminder'] = $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getValue();
  }
}

$eventDate = $row;

foreach ($eventDate as $eventKey => $eventValue) {
  $arrayDate[$eventKey] = $eventValue['date'];
}
array_multisort($arrayDate, SORT_ASC, $eventDate);
unset($eventKey);
unset($eventValue);
unset($arrayDate);

foreach ($eventDate as $eventValue) {
  $eventTime = strtotime('-15 minutes', $eventValue['date']);
  if (time() < $eventTime) {
    $arrayDate[] = $eventTime;
  }
}

$nextDate = array_slice($arrayDate, 0, 1)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Лист событий</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
  <style>
    body {
      font-size: 12px;
    }
    .info {
      padding-top: 25px;
      font-size: 14px;
    }
    .info .label-default {
      background-color: #c3c3c3;
    }
  </style>
</head>
<body>
<div class="container">
<div class="row">
  <div class="col-md-6">
    <h2>Лист событий</h2>
  </div>
  <div class="col-md-6">
    <p class="pull-right info">
      <span class="label label-success">Отправлено</span>
      <span class="label label-danger">Ближайшее</span>
      <span class="label label-default">Ожидает</span>
    </p>
  </div>
</div>
  <table class="table table-bordered table-hover">
  <tr>
    <th width="15%"><?php echo $objPHPExcel->getActiveSheet()->getCell('A' . 1)->getValue(); ?></th>
    <th width="15%"><?php echo $objPHPExcel->getActiveSheet()->getCell('B' . 1)->getValue(); ?></th>
    <th width="35%"><?php echo $objPHPExcel->getActiveSheet()->getCell('C' . 1)->getValue(); ?></th>
    <th width="35%"><?php echo $objPHPExcel->getActiveSheet()->getCell('D' . 1)->getValue(); ?></th>
  </tr>
  <?php
    foreach ($row as $item) {
      if (strtotime('-15 minutes', $item['date']) === $nextDate) {
        echo '<tr class="danger">';
      } else if (time() < strtotime('-15 minutes', $item['date'])) {
        echo '<tr class="active">';
      } else {
        echo '<tr class="success">';
      }
        echo '<td>' . $item['pass'] . '</td>';
        echo '<td>' . date('d.m.y G:i', $item['date']) . '</td>';
        echo '<td>' . $item['event'] . '</td>';
        echo '<td>' . $item['reminder'] . '</td>';
      echo '</tr>';
    }
  ?>
  </table>
</div>
</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</html>