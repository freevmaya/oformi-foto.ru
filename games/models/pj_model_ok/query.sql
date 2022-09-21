// Количество открыток отправленных раньше чем началась регистрация в gpj_options
SELECT COUNT(s.uid) 
FROM `gpj_send` s LEFT JOIN `gpj_options` o ON s.sendTo=o.uid
WHERE isNULL(o.uid)

DELETE 
FROM gpj_send 
WHERE sendTo NOT IN (SELECT uid FROM gpj_options);


SELECT * 
FROM `gpj_send` s LEFT JOIN `gpj_options` o ON s.sendTo=o.uid
WHERE isNULL(o.uid)
ORDER BY s.uid
LIMIT 0, 20


SELECT *
FROM `gpj_send` s
WHERE (SELECT COUNT(uid) FROM `gpj_options` o WHERE o.uid=s.sendTo)=0 
ORDER BY s.uid
LIMIT 0, 20


// Выбирает открытки если пользователь адресат не заходил в приложение с 2010-09-01, можно использовать DELETE 
SELECT *
FROM gpj_send 
WHERE sendTo NOT IN (SELECT uid FROM gpj_options WHERE (`visitDate`>'2010-09-01' OR `visitDate`='0000-00-00'));

// Выбирает открытки с датой меньше 2010-09-01, если пользователь адресат так и не зашел в приложение, можно использовать DELETE 
SELECT *
FROM gpj_send 
WHERE sendTo IN (SELECT uid FROM gpj_options WHERE `visitDate`='0000-00-00') AND `time`<'2010-09-01 00:00:00' AND `received`=0;


//Показывает группы запросов, отсортированные по времени исполнения
SELECT `query`, SUM(`timeCount`) AS  `time`
FROM `query_statistic`
GROUP BY `query`
ORDER BY `time`

//Показать всех пользователей которые не заходили с 01.06.2012 года
SELECT * FROM `pjok_options` WHERE `visitDate`<'2012-06-01' AND `visitDate`>'0000-00-00'