# Менеджер Уроков
Бот присылает расписание 2 раза в сутки, за 5 часов и за 15 мин. до занятия.
## Условия работы в текущей версии
Расписание в формате `xlsx`. Опрос бота `excel.php` с помощью cron каждые 15 мин. или чаще если требуется.
### excel.php
* Шаблон расписания: https://yadi.sk/d/KRD-MTMV3LunxW
* Имя файла в директории должно быть таким: `rasp.xlsx`
* Столбцы/поля должны быть по шаблону, в качестве даты указывается точная дата занятия
Пример работы с расписанием
```
if (time() >= strtotime('-8 hours -1 minutes', $date) && time() <= strtotime('-7 hours -59 minutes', $date)) {
    $timing[$i]['reminder']['pass'] = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
    $timing[$i]['reminder']['text'] = $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getValue();
    }
if (time() >= strtotime('-3 hours -16 minutes', $date) && time() <= strtotime('-3 hours -14 minutes', $date)) {
    $timing[$i]['event']['pass'] = $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
    $timing[$i]['event']['text'] = $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getValue();
}
```
Когда время вызова скрипта через cron совпадает с временем в таблице, значения из таблицы попадают в массивы, где:
* **reminder** - оповещение о занятии за 5 часов (-3 часа смещение серверного времени по мск и -5 часов само смещение)
* **event** - само событие (-3 часа смещение серверного времени по мск и -15 мин, чтобы напоминание было за 15 мин. до занятия)
В файле `passlist.json` хранится список `id` пользователй которые указали пароль к группе, присоединившись тем самым к нужному потоку:
```
$IDandPass = file_get_contents(__DIR__ . '/passlist.json');
$IDandPass = json_decode($IDandPass, true);

if (isset($timing)) {
  foreach ($timing as $time) {
        //...
    }
}
```
Напоминания в таблице может и не быть (см. шаблон расписания, расписание на неделю) поэтому сначала проверка на наличие данных:
```
if (isset($time['reminder']['text'])) {
    foreach($IDandPass as $user) {
        if ($time['reminder']['pass'] === array_values($user)[0]) {
            $lessonManager->sendMessage($time['reminder']['text'], array_keys($user)[0]);
        }
    }
}

if (isset($time['event']['text'])) {
    foreach($IDandPass as $user) {
        if ($time['event']['pass'] === array_values($user)[0]) {
            $lessonManager->sendMessage($time['event']['text'], array_keys($user)[0]);
        }
    }
}
```
Далее происходит проверка: если пароль из `passlist.json` совпадает с паролем группы в табличке, значит этому пользователю приходит месседж.
Все дело в том, что телеграм не предоставляет никаких уникальных данных кроме `id` чата, поэтому изначально рассматривалось 2 варианта решения:
* Пользователь через [userinfobot](https://telegram.me/userinfobot) сам смотрит свой `id` и сообщает его администратору для распределения по потокам, что довольно нагрузочно для администратора.
* Пользователь сам присоединяет себя к потоку в режиме диалога с ботом, через пароль группы который будет выдавать администратор.
Возможно идентификация по паролю и не требуется и достаточно будет обойтись кодом группы типа "PHP-n" и др. но тогда пользователь при желании может перебирать идентификаторы групп и подписываться на расписания к следующим потокам, не знаю насколько это критично.
### index.php
В классе `LessonManager` набор методов для работы бота.
#### arrayData()
Принимает и разбирает `update` в формате `json`.
#### commandController()
Выполняет различные сценарии исходя из данных обработанных `arrayData()`.
#### saveUserData()
Сохраняет в `json` данные введенные пользователем. Это может потребоваться для выполения многошаговых сценариев. Данные сохраняются в отдельных файликах, где имя файла = уникальный `id` чата.
#### saveUserPass()
Сохраняет в `json` соответствия `id` чата/пароль группы.
#### sendMessage()
Отправляет сообщением любое, что передается.
#### log()
Для помощи в отладке, сохраняет единоразово в `log.txt` текущий `update`.

