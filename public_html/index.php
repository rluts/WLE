<?php
if(!empty($_GET['id'])) {
	$id = $_GET['id'];
} else {
	if(!empty($_GET['region'])) $region = mysql_escape_string($_GET['region']);
	if(!empty($_GET['title'])) $title = mysql_escape_string($_GET['title']);
	if(!empty($_GET['type'])) $type = mysql_escape_string($_GET['type']);
	if(!empty($_GET['location'])) $location = mysql_escape_string($_GET['location']);
	if(!empty($_GET['photo'])) $photo = true;
}

if(isset($id) && preg_match_all('/([0-9]{2})-([0-9]{3})-([0-9]{4})/', $id, $matches)) {
	$ident = $matches[1][0] . $matches[2][0] . $matches[3][0];
	$query = "SELECT * FROM list WHERE id='$ident';";	
	$c = gettable($query);	
}
else {
	$where = array();
	if(isset($region) || isset($title) || isset($type) || isset($location) || isset($photo)) {
		if(isset($region)) $where[] = "region LIKE '$region%'";
		if(isset($title)) $where[] = "title LIKE '%$title%'";
		if(isset($type)) $where[] = "type LIKE '%$type%'";
		if(isset($location)) $where[] = "location LIKE '%$location%'";
		if(isset($photo)) $where[] = "photo <> ''";
		$query = "SELECT * FROM list WHERE ";
		$counter = count($where); $i = 0;
		foreach($where as $value) {
			if(++$i != $counter) $after = ' AND '; else $after = ';';
			$query .= $value . $after;
		}
		$c = gettable($query);		
	} else {	
	if(isset($id)) $res = "Невірний формат ID.<br />";
	else $res = '';	
	$c = $res . '
	<div id="search">
	Введіть ID або інші параметри:
	<form action="/wle/">
			<div class="inpwrp">
			<div class="left">Введіть ID:</div>
			<div class="right"><input type="text" name="id"/></div>
			</div>
			
			<div class="inpwrp">
			<div class="left">Область:</div>
			<div class="right"><input type="text" name="region" /></div>
			</div>
			<div class="inpwrp">
			<div class="left">Назва:</div>
			<div class="right"><input type="text" name="title" /></div>
			</div>
			<div class="inpwrp">
			<div class="left">Тип пам\'ятки:</div>
			<div class="right"><input type="text" name="type" /></div>
			</div>
			<div class="inpwrp">
			<div class="left">Розташування:</div>
			<div class="right"><input type="text" name="location" /></div>
			</div>
			<div class="right"><input type="checkbox" name="photo" value="1" />тільки з фото</div>
			<div class="right"><input type="submit"/></div>
	</form></div>';
	}
}

$content['title'] = 'Wiki Loves Earth';
$content['title2'] = "<a href=\"/wle/\">Wiki Loves Earth Ukraine</a>";
$content['scripts'] = str_replace('{$home}', 'wle', file_get_contents('headsorter.html'));
$content['content'] = $c;
require_once('template.php');

/////////////////////////////////////////////////////////////////////
//Functions
function wikiparse($text) {
	$ret = preg_replace('/\[\[([^\|]+)]]/i', '<a target=\"_blank\" href="https://uk.wikipedia.org/wiki/$1">$1</a>', $text);
	$ret = preg_replace('/\[\[(.+?)\|(.+?)]]/i', '<a target=\"_blank\" href="https://uk.wikipedia.org/wiki/$1">$2</a>', $ret);
	return $ret;
}

function gettable($query) {
	require_once('botclasses.php');
	require_once('config.php');
	$connection = new mysqli('tools-db', $dbuser, $dbpass, $dbname);
	if ($connection->connect_error) {
		$content['content'] = "Connect error: " . mysqli_connect_error();
		require_once('template.php');
		exit();
	}
	$connection->query('SET NAMES utf8');
	$result = $connection->query($query);
	$text = '<table id="table" class="tablesorter">
	<thead> 
	<tr> 
		<th>ID</th> 
		<th>Назва</th> 
		<th>Зображення</th>
		<th>Розташування</th>
		<th>Користувач</th>
		<th>Площа/Координати</th>
		<th>Тип</th>
		<th>Джерело</th>
		<th>Галерея у Вікісховищі</th>
	</tr> 
	</thead>';
	while($row = $result->fetch_array(MYSQLI_ASSOC)) {
		$api = new wikipedia('https://commons.wikimedia.org/w/api.php');
		
		//Format ID
		$id=$row['id'];
		$idp1 = floor($id/10000000);
		$idp2 = floor(($id-$idp1*10000000)/10000);
		$idp3 = $id-$idp1*10000000-$idp2*10000;
		if($idp1<10) $idp1 = '0' . $idp1;
		$id = $idp1 . "-" . $idp2 . "-" . $idp3;
		
		//Format image
		if($row['photo']) {
			$image = '<a target=\"_blank\" href="https://commons.wikimedia.org/wiki/File:' . rawurlencode($row['photo']) . '"><img src="' . str_replace('/commons/', '/commons/thumb/', $api->getfilelocation('File:' . $row['photo'])) . "/100px-" . rawurlencode(str_replace(' ', '_', $row['photo'])) . '"></img></a>';
		}
		else $image = "";
		
		//Format Source
		$source = "<a target=\"_blank\" href=\"https://uk.wikipedia.org/wiki/Вікіпедія:Вікі_любить_Землю/" . rawurlencode($row['region']) . "\">Вікіпедія:Вікі любить Землю/" . $row['region'] . "</a>";
		
		//Format Commons Category
		if($row['galery']) {
			$category = "<a target=\"_blank\" href=\"https://commons.wikimedia.org/wiki/Category:". $row['galery'] . "\">{$row['galery']}</a>";
		}				
		else $category = '';
		
		//Format Coords
		if($row['lat'] && $row['longit']) {
			$coords = "<a target=\"_blank\" href=\"http://www.openstreetmap.org/?mlat={$row['lat']}&mlon={$row['longit']}&zoom=14\">{$row['lat']}, {$row['longit']}</a>";
		} else {
			$coords = "—";
		}
		
		$text .= "<tr><td>$id</td><td>".wikiparse($row['title'])."</td><td>$image</td><td>".wikiparse($row['location'])."</td><td>".wikiparse($row['owner'])."</td><td>{$row['area']}<br/>$coords</td><td>".wikiparse($row['type'])."<br />".wikiparse($row[10])."</td><td>$source</td><td>$category</td></tr>";
	}
	$text .= '</tbody> 
		</table>';
		return $text;
}
?>