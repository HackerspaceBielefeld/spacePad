<?php
if(!file_exists('config.php')) {
	header('Location: install.php');
}else if(file_exists('install.php')) {
	echo 'install.php muss entfernt werden.';
	die();
}

require('config.php');
require('func.php');

if(!isset($_GET['i']) && isset($_POST['submit'])) {
	$id = $sql->set("INSERT INTO docs (docName) VALUES (?)","s",array($_POST['name']),true);
	if($id) {
		$sql->set("INSERT INTO `lines` (docID) VALUES (?)","i",array($id));
		header('Location: ./?i='.$id);
		die();
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Space-Pad</title>
		<link rel="stylesheet" href="/style.css" type="text/css" />
		<script src="/jquery.js" type="text/javascript" charset="utf-8"></script>
		<script src="/pad.js" type="text/javascript" charset="utf-8"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	</head>
	<body id="index">
<?php
//altes zeug wegräumen
$sql->set("DELETE FROM writer WHERE writerTimeout < NOW();");

$alt = date("Y-m-d H:i:s",time()-(60*60*24));
$sql->set("DELETE FROM changes WHERE changed < ?",'s',array($alt));
/////////////////////////////////////////
if(isset($_GET['i'])) {
	$doc = $sql->get("SELECT * FROM docs WHERE docID = ?","i",array($_GET['i']));
	if($doc) {
		do {
			$cookie = random(75);
			$userID = $sql->set("INSERT INTO writer (writerCookie,writerTimeout) VALUES (?,?)","ss",array($cookie, date("Y-m-d H:i:s",time())),true);
		} while(!$userID);
		
		echo '<h1>Space-Pad: '. $doc[0]['docName'] .'</h1>
			<script type="text/javascript" charset="utf-8">var doc = '. $_GET['i'] .'; var last = '. $doc[0]['lastChange'] .'; var cookie = "'. $cookie .'"</script>
		<div class="document">';
			
		$lines = $sql->get("SELECT * FROM `lines` WHERE docID = ? ORDER BY lineID","i",array($_GET['i']));
		$dann = 0;
		if($lines) {
			$zeilen = array();
			$folge = array();
			foreach($lines as $line) {
				if($dann == 0) {
					$dann = $line['lineID'];
				}
				$zeilen[$line['lineID']] = array('content'=>$line['content'],'danach'=>$line['danach']);
			}
			
			$c = count($zeilen);
			for($i=0;$i<$c;$i++) {
				if($dann == 0)
					break;
				echo '<div id="content_'. $dann .'" class="content">
					<div id="content_div_'. $dann .'" class="content_div">'. ($zeilen[$dann]['content'] != ''?$zeilen[$dann]['content']:'&nbsp;') .'</div>
					<textarea id="content_text_'. $dann .'" class="content_text" style="display:none;">'. $zeilen[$dann]['content'] .'</textarea>
				</div>';
				$dann = $zeilen[$dann]['danach'];
			}			
		}
		echo '</div>';
	}
}else{
	echo '<h1>Space-Pad</h1><script type="text/javascript" charset="utf-8">var doc = 0;</script>';
	$data = $sql->get("SELECT * FROM docs ORDER BY docLast DESC");
	if($data) foreach($data as $d) {
		echo '<a href="/?i='. $d['docID'] .'" class="list">'. $d['docName'] .'</a>';
	}
	echo '<div>
		<form action="index.php" method="post">
			Neues Pad
			<input name="name" />
			<input type="submit" name="submit" value="erstellen" />
		</form>
	</div>';
}
?>
		</div>
	</body>
</html>
