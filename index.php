<?php

/**
* LessonManager
*/
class LessonManager
{
	private $apiURL = 'https://api.telegram.org/bot';
	private $token = '303163641:AAESIyEByW9UzatswuE7Ay679ZYksHNYbLc';
	const PASSLIST = 'passlist.json';
	const COMMAND_DUMP = 'command-dump/';

	function __construct()
	{
		if (isset($this->arrayData()['chatID'])) {
			$this->commandController();
		}
	}

	public function arrayData()
	{
		$data = file_get_contents('php://input');
		$data = json_decode($data, true);

		$this->log($data);

		$arrayData = [
			'chatID' => $data['message']['chat']['id'], // Чат ID
			'text' => $data['message']['text'] // Содержание message
		];

		return $arrayData;
	}

	public function commandController()
	{
		// Если существует json с ID пользователя
		if (file_exists(self::COMMAND_DUMP . $this->arrayData()['chatID'] . '.json')) {
			$userData = file_get_contents(self::COMMAND_DUMP . $this->arrayData()['chatID'] . '.json');
			$userData = json_decode($userData, true);

			// Если введена одна из команд
			// Пусть /start отправляет на /setPassword
			if ($this->arrayData()['text'] === '/start') {
				$this->sendMessage('Выполните /setPassword чтобы настроить пароль группы.');
			} else if ($this->arrayData()['text'] === '/setPassword') {
				$this->sendMessage('Введите пароль группы.');
			} else {
				// Иначе воспринимаем как текст и обрабатываем
				// Пусть /start отправляет на /setPassword
				if ($userData['userCommand'] === '/start') {
					$this->sendMessage('Выполните /setPassword чтобы настроить пароль группы.');
				} else if ($userData['userCommand'] === '/setPassword') {
					$this->saveUserPass();
					$this->sendMessage('Пароль сохранен, рассылка начнется как только - так сразу :)');
				} else {
					$this->sendMessage('Команда не была распознана. Попробуйте еще раз.');
				}
			}
			$this->saveUserData();
		} else {
			$this->saveUserData();
			$this->sendMessage('Выполните /setPassword чтобы настроить пароль группы.');
		}
	}

	public function saveUserData()
	{
			$arrayData = [
				'userCommand' => $this->arrayData()['text']
			];
			file_put_contents(self::COMMAND_DUMP . $this->arrayData()['chatID'] . '.json', json_encode($arrayData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

	public function sendMessage($text, $chatID = null)
	{
		if (is_null($chatID)) {
			$chatID = $this->arrayData()['chatID'];
		}

		$message = [
			'method' => 'sendMessage',
			'parse_mode' => 'Markdown',
			'chat_id' => $chatID,
			'text' => $text
		];

		return file_get_contents($this->apiURL . $this->token . '/?' . http_build_query($message));
	}

	public function saveUserPass()
	{
		if (file_exists(self::PASSLIST)) {
			$passList = file_get_contents(self::PASSLIST);
			$passList = json_decode($passList, true);

			// Удаляем старый пароль
			foreach ($passList as $item) {
				if (array_key_exists($this->arrayData()['chatID'], $item) === true) {
					continue;
				}

				$passListArray[] = $item;
			}

			$passListArray[] = [
				$this->arrayData()['chatID'] => $this->arrayData()['text']
			];

			file_put_contents(self::PASSLIST, json_encode($passListArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		} else {
			$passList[] = [
				$this->arrayData()['chatID'] => $this->arrayData()['text']
			];

			file_put_contents(self::PASSLIST, json_encode($passList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		}
	}
	
	public function log($data)
	{
		$data = print_r($data, true);
		return file_put_contents(__DIR__ . '/log.txt', $data);
	}
}

$lessonManager = new LessonManager;