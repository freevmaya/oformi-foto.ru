CREATE TABLE IF NOT EXISTS `clt_vkgame` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` bigint(20) unsigned NOT NULL,
  `rate` bigint(20) unsigned NOT NULL,
  `time` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `byTime` (`time`),
  KEY `byRate` (`rate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2966 ;

-- --------------------------------------------------------

--
-- Структура таблицы `clt_vktransaction`
--

CREATE TABLE IF NOT EXISTS `clt_vktransaction` (
  `transaction_id` int(11) NOT NULL,
  `time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `user_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `sms_price` double default '0',
  `other_price` double default '0',
  `debug` smallint(1) default '0',
  `state` char(60) NOT NULL default '0',
  `params` text,
  `param_num` bigint(20) unsigned default '0',
  PRIMARY KEY  (`transaction_id`),
  KEY `by_userID` (`user_id`),
  KEY `by_time` (`time`),
  KEY `by_service` (`service_id`),
  KEY `byUidAndTime` (`user_id`,`time`),
  KEY `byNumParam` (`param_num`),
  KEY `byTS` (`time`,`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `clt_vkvotes`
--

CREATE TABLE IF NOT EXISTS `clt_vkvotes` (
  `game_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `votes` smallint(6) NOT NULL,
  PRIMARY KEY  (`game_id`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- План определения побед .8062938299454250872

-- Собираем все коллажи проверяемого пользователя, период действия которых уже кончился и победа не определена

SELECT * FROM `clt_game` WHERE `uid`={$uid} AND noVictory=0 AND `time`<=NOW()-INTERVAL 1 DAY

-- Собираем все коллажи кроме проверяемого коллажа, начало действия которых в периоде действия проверяемого
-- Сортируем по рейтингу и получаем максимальный рейтинг на дату окончания проверяемого коллажа

SELECT *,
    (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<={$endTime}) AS votes 
FROM `clt_game` g 
WHERE g.`id`<>{$game_id} AND g.`time`>={$startTime} AND g.`time`<={$endTime}
ORDER BY votes DESC
LIMIT 0,1
