<?php
header('Content-Type	text/html; charset=UTF-8');
$regions = array('АР Крим', 'Київ', 'Севастополь', 'Волинська область', 'Вінницька область', 'Дніпропетровська область', 'Донецька область', 'Житомирська область', 'Закарпатська область', 'Запорізька область', 'Івано-Франківська область', 'Київська область', 'Кіровоградська область', 'Луганська область', 'Львівська область', 'Миколаївська область', 'Одеська область', 'Полтавська область', 'Рівненська область', 'Сумська область', 'Тернопільська область', 'Харківська область', 'Херсонська область', 'Хмельницька область', 'Черкаська область', 'Чернівецька область', 'Чернігівська область');

//////////////////////////

require_once('/data/project/wle/public_html/config.php');
require_once('/data/project/wle/public_html/botclasses.php');

$connection = new mysqli('tools-db', $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') '
			. $connection->connect_error);
}
$connection->query('SET NAMES utf8');

$bot = new wikipedia('https://uk.wikipedia.org/w/api.php');
$i=1;
foreach($regions as $region) {
	$page = 'Вікіпедія:Вікі любить Землю/' . $region;
	$content = $bot->getpage($page);
	#echo $content;

	preg_match_all('/\{\{ВЛЗ-рядок\s*?\|\s*?ID\s*?=\s*?([0-9]{1,2})-([0-9]{1,3})-([0-9]{1,4})\s*?\|\s*?назва\s*?=\s*?(.*?)\s*?\|\s*?постанова\s*?=\s*?(.*?)\s*?\|\s*?розташування\s*?=\s*?(.*?)\s*?\|\s*?користувач\s*?=\s*?(.*?)\s*?\|\s*?площа\s*?=\s*?(.*?)\s*?\|\s*?широта\s*?=\s*?(.*?)\s*?\|\s*?довгота\s*?=\s*?(.*?)\s*?\|\s*?тип\s*?=\s*?(.*?)\s*?\|\s*?підтип\s*?=\s*?(.*?)\s*?\|\s*?фото\s*?=\s*?(.*?)\s*?\|\s*?галерея\s*?=\s*?(.*?)\s*?}}/', $content, $matches);

	$array = array();
	foreach($matches[0] as $key=>$value) {
		$array[$key]['id'] = $connection->escape_string(trim($matches[1][$key])) . $connection->escape_string(trim($matches[2][$key])) . $connection->escape_string(trim($matches[3][$key]));
		$array[$key]['title'] = $connection->escape_string(trim($matches[4][$key]));
		//$array[$key]['resolution'] = $connection->escape_string(trim($matches[5][$key]));
		$array[$key]['situation'] = $connection->escape_string(trim($matches[6][$key]));
		$array[$key]['owner'] = $connection->escape_string(trim($matches[7][$key]));
		$array[$key]['area'] = $connection->escape_string(trim($matches[8][$key]));
		$array[$key]['lat'] = $connection->escape_string(trim($matches[9][$key]));
		$array[$key]['lon'] = $connection->escape_string(trim($matches[10][$key]));
		$array[$key]['type'] = $connection->escape_string(trim($matches[11][$key]));
		$array[$key]['subtype'] = $connection->escape_string(trim($matches[12][$key]));
		$array[$key]['photo'] = $connection->escape_string(trim($matches[13][$key]));
		$array[$key]['galery'] = $connection->escape_string(trim($matches[14][$key]));
		
		$query = "INSERT INTO list VALUES('{$array[$key]['id']}', '$region', '{$array[$key]['title']}', '{$array[$key]['situation']}', '{$array[$key]['owner']}', '{$array[$key]['area']}', '{$array[$key]['lat']}', '{$array[$key]['lon']}', '{$array[$key]['type']}', '{$array[$key]['subtype']}', '{$array[$key]['photo']}', '{$array[$key]['galery']}') ON DUPLICATE KEY UPDATE id='{$array[$key]['id']}', region='$region', title='{$array[$key]['title']}', location='{$array[$key]['situation']}', owner='{$array[$key]['owner']}', area='{$array[$key]['area']}', lat='{$array[$key]['lat']}', longit='{$array[$key]['lon']}', type='{$array[$key]['type']}', subtype='{$array[$key]['subtype']}', photo='{$array[$key]['photo']}', galery='{$array[$key]['galery']}'";
#		$query = "";
		echo "$i. $query<br>";
		$i++;
		if($connection->query($query)) echo "<span style=\"color:green\">OK</span><br>"; else echo "<span style=\"color:red\">Fail</span>: " . $connection->error . "<br>";
	}
}
$connection->close();


?>