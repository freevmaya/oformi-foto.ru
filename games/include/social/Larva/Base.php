<?php
	
	/*
	* (c) Pavel Ladygin (digitorum) http://digitorum.ru
	*/
	
	Class Social_Larva_Base {
		
		/*
		* Печеньки для запросов
		*/
		protected $_cookies = NULL;
		
		/*
		* Закэшированные данные
		*/
		protected $_cache = array();
		
		/*
		* Юзерагент
		*/
		protected $_userAgent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.75 Safari/535.7';
		
		/*
		* Базовое доменное имя
		*/
		protected $_baseDomain = '';
		
		/*
		* Базовый протокол
		*/
		protected $_baseProtocol = 'http';
		
		/*
		* Установить юзерагент
		*/
		public function setUserAgent($userAgent = '') {
			$this->_userAgent = $userAgent;
		}
		
		/*
		* Запомнить значение
		*/
		protected function _setCacheValue($key = '', $value = '') {
			$this->_cache[$key] = $value;
		}
		
		/*
		* Вспомнить значение
		*/
		protected function _getCacheValue($key = '') {
			 return isset($this->_cache[$key]) ? $this->_cache[$key] : false;
		}
		
		/*
		* Удалить кэш
		*/
		protected function _clearCache($key = '') {
			$this->_cache = array();
		}
		
		/*
		* Получить список печенегк из ответа
		*/
		protected function _getCookiesFromResponse($result) {
			$cookies = array();
			preg_match_all('~Set-Cookie:\s*([^;]+)~i', $result, $cookies);
			if(!count($cookies)) {
				return array();
			}
			return $cookies[1];
		}
		
		/*
		* Получить редирект из ответа
		*/
		protected function _getLocationHeaderFromResponce($result) {
			$location = array();
			if(preg_match('~Location:\s*([^\n$]+)~is', $result, $location)) {
				return $location[1];
			}
			return false;
		}
		
		/*
		* Отправляем запрос
		*/
		protected function _sendRequest($options = array()) {
			$ch = curl_init();
			foreach($options as $option => $value) {
				curl_setopt($ch, $option, $value);
			}
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		    curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		    $result = curl_exec($ch);
			if ($result) {
				curl_close($ch);
				return $result;
			} else {
				$error = curl_error($ch);
				curl_close($ch);
				throw new Social_Larva_Exception($error);
			}
		}
		
		/*
		* Получить форму со страницы
		*/
		protected function _getFormHtml($pageHtml, $formUniqueAttribute) {
			if(preg_match('~<form[^<]+' . $formUniqueAttribute . '.*?</form>~is', $pageHtml, $matches)) {
				return $matches[0];
			}
			return '';
		}
		
		/*
		* Получаем поля формы
		*/
		protected function _getFormFields($formHtml) {
			$fields = array();
			// пока забираем только инпуты
			preg_match_all('~<input[^>]+>~is', $formHtml, $matches);
			foreach($matches[0] as $match) {
				preg_match_all('~(<|name|type|value)[^\s]+~is', $match, $attributes);
				$tmp = array();
				foreach($attributes[0] as $attribute) {
					$attribute = explode("=", str_replace(array('\'', '"', '<'), array('','',''), $attribute));
					if(count($attribute) == 1) {
						$tmp['element'] = $attribute[0];
					} else {
						$tmp[$attribute[0]] = $attribute[1];
					}
				}
				if(isset($tmp['name']) && isset($tmp['element'])) {
					switch($tmp['element']) {
						case 'input' :
							if(isset($tmp['value'])) {
								$fields[$tmp['name']] = $tmp['value'];
							}
							break;
						default :
							break;
					}
					
				}
			}
			return $fields;
		}
		
		/*
		* Разбираем форму
		*/
		protected function _parseForm($formHtml = '') {
			$result = array(
				'fields' => $this->_getFormFields($formHtml),
				'action' => '',
				'method' => ''
			);
			if(preg_match_all('~(action=|method=).*?\s~is', $formHtml, $matches)) {
				foreach($matches[0] as $match) {
					$match = explode('=', str_replace(array('\'', '"'), array('', ''), $match), 2);
					if(count($match) == 2) {
						$result[strtolower($match[0])] = trim($match[1]);
					}
				}
			}
			if($result['method'] == '') {
				throw new Social_Larva_Exception('No request method detected');
			}
			if($result['action']  == '') {
				throw new Social_Larva_Exception('No form action detected');
			}
			$result['method'] = strtoupper($result['method']);
			if(!preg_match('~https?://~', $result['action'])) {
				$result['action'] = $this->_baseProtocol . '://' . $this->_baseDomain . $result['action'];
			}
			return $result;
		}
		
		/*
		* Получить главную страницу
		*/
		protected function _getMainPageHtml() {
			$url = $this->_baseProtocol . '://' . $this->_baseDomain;
			if(!$this->_getCacheValue($url)) {
				$this->_setCacheValue(
					$url,
					$this->_sendRequest(
						array(
							CURLOPT_URL => $url,
							CURLOPT_COOKIE => $this->_cookies
						)
					)
				);
			}
			return $this->_getCacheValue($url);
		}
		
	}
	
?>