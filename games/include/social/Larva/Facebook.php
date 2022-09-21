<?php
	
	/*
	* (c) Pavel Ladygin (digitorum) http://digitorum.ru
	*/
	
	Class Social_Larva_Facebook extends Social_Larva_Base {
		
		/*
		* Базовый протокол
		*/
		protected $_baseProtocol = 'https';
		
		/*
		* Базовое доменное имя
		*/
		protected $_baseDomain = 'www.facebook.com';
		
		/*
		* Конструктор
		*/
		public function __construct($email = '', $password = '') {
			$this->_login($email, $password);
		}
		
		/*
		* Деструктор
		*/
		public function __destruct() {
			$this->_logout();
		}
		
		/*
		* Авторизация
		*/
		private function _login($email = '', $password = '') {
			$form = $this->_parseForm($this->_getFormHtml($this->_getMainPageHtml(), 'login_form'));
			$form['fields']['email'] = $email;
			$form['fields']['pass'] = $password;
			$result = $this->_sendRequest(
				array(
					CURLOPT_URL => $form['action'],
					CURLOPT_HEADER => true,
					CURLOPT_NOBODY => true,
					CURLOPT_CUSTOMREQUEST => $form['method'],
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($form['fields']),
					CURLOPT_COOKIE => NULL
				)
			);
			$this->_cookies = $this->_getCookiesFromResponse($result);
			if(!count($this->_cookies)) {
				throw new Social_Larva_Exception('Can\'t receive cookies at first step authorization');
			}
			$this->_cookies = $this->_cookies[0];
			$result = $this->_sendRequest(
				array(
					CURLOPT_URL => $form['action'],
					CURLOPT_HEADER => true,
					CURLOPT_NOBODY => true,
					CURLOPT_CUSTOMREQUEST => $form['method'],
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($form['fields']),
					CURLOPT_COOKIE => $this->_cookies
				)
			);
			$this->_cookies = implode(';', $this->_getCookiesFromResponse($result));
			if(strpos($this->_cookies, 'c_user') === false) {
				// если не получилось авторизоваться - выбрасываем эксепшн
				throw new Social_Larva_Exception('Authorization failed');
			}
			// сбрасываем весь кэш после авторизации
			$this->_clearCache();
		}
		
		/*
		* Выход
		*/
		private function _logout() {
			if($this->_cookies !== NULL) {
				// ищем форму и поля в ней
				$form = $this->_parseForm($this->_getFormHtml($this->_getMainPageHtml(), 'logout_form'));
				// отправляем запрос
				$result = $this->_sendRequest(
					array(
						CURLOPT_URL => $form['action'],
						CURLOPT_HEADER => true,
						CURLOPT_CUSTOMREQUEST => $form['method'],
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => http_build_query($form['fields']),
						CURLOPT_COOKIE => $this->_cookies
					)
				);
				// смотрим не удалялась ли кука
				if(strpos($result, 'c_user=deleted') !== false) {
					$this->_cookies = NULL;
					$this->_clearCache();
					return true;
				}
			}
			return false;
		}
		
		/*
		* Обновить статус
		*/
		public function updateStatus($statusText = '', $privacy = false) {
			// ищем форму и поля в ней
			$form = $this->_parseForm($this->_getFormHtml($this->_getMainPageHtml(), '/ajax/updatestatus.php'));
			$form['fields']['xhpc_message'] = $statusText;
			$form['fields']['xhpc_message_text'] = $form['fields']['xhpc_message'];
			if($privacy) {
				switch(true) {
					case $privacy == 'Public' :
						$form['fields']['audience[0][value]'] = 80;
						break;
					case $privacy == 'Friends' :
						$form['fields']['audience[0][value]'] = 40;
						break;
					case $privacy == 'Only me' :
						$form['fields']['audience[0][value]'] = 10;
						break;
					default:
						$form['fields']['audience[0][value]'] = $privacy;
						break;
				}
			}
			// отправляем запрос
			$result = $this->_sendRequest(
				array(
					CURLOPT_URL => $form['action'],
					CURLOPT_HEADER => true,
					CURLOPT_NOBODY => false,
					CURLOPT_CUSTOMREQUEST => $form['method'],
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($form['fields']),
					CURLOPT_COOKIE => $this->_cookies
				)
			);
			// если статус обновился - есть локейшн в заголовках
			if(strpos($result, 'Location:') !== false) {
				return true;
			}
			return false;
		}
		
	}
	
?>