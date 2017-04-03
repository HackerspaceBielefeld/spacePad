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
	<body>
<?php
if(isset($_POST['submit'])) {
	$host = $_POST['host'];
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$data = $_POST['data'];

	require('func.php');
	
	$sql->set("CREATE TABLE IF NOT EXISTS `changes` (
	  `changeID` int(11) NOT NULL AUTO_INCREMENT,
	  `docID` int(11) NOT NULL,
	  `json` text NOT NULL,
	  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`changeID`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	echo '<div>Changes Tabelle erzeugt...</div>';
	
	
	$sql->set("CREATE TABLE IF NOT EXISTS `docs` (
	  `docID` int(11) NOT NULL AUTO_INCREMENT,
	  `docName` varchar(55) CHARACTER SET utf8 NOT NULL,
	  `docLast` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `lastChange` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`docID`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	echo '<div>Docs Tabelle erzeugt...</div>';
	
	$sql->set("CREATE TABLE IF NOT EXISTS `line` (
	  `lineID` int(11) NOT NULL AUTO_INCREMENT,
	  `docID` int(11) NOT NULL,
	  `content` text CHARACTER SET utf8 NOT NULL,
	  `exclusive` varchar(75) CHARACTER SET utf8 NOT NULL DEFAULT '',
	  `danach` int(11) NOT NULL,
	  PRIMARY KEY (`lineID`) USING BTREE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	echo '<div>Line Tabelle erzeugt...</div>';
	
	$sql->set("CREATE TABLE IF NOT EXISTS `writer` (
	  `writerID` int(11) NOT NULL AUTO_INCREMENT,
	  `writerName` varchar(35) NOT NULL,
	  `writerCookie` varchar(75) NOT NULL,
	  `writerTimeout` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`writerID`),
	  UNIQUE KEY `writerCookie` (`writerCookie`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	echo '<div>Writer Tabelle erzeugt...</div>';
	
	echo '<div>Nun muss eine "config.php" angelegt werden mit folgendem Inhalt:</div>
	<div><textarea style="width: 300px; height: 100px;"><?php
	$host = "'.$host.'";
	$user = "'.$user.'";
	$pass = "'.$pass.'";
	$data = "'.$data.'";
?></textarea></div>';
}else{
	echo '<form action="install.php" method="post">
		<div><span style="width: 100px; display:block;">Server</span><input type="text" name="host" value="localhost" /></div>
		<div><span style="width: 100px; display:block;">User</span><input type="text" name="user" /></div>
		<div><span style="width: 100px; display:block;">Passwort</span><input type="password" name="pass" /></div>
		<div><span style="width: 100px; display:block;">Datenbank</span><input type="text" name="data" /></div>
		<div><input type="submit" name="submit" value="Installieren"/></div>
	</form>';
}
?>
	</body>
</html>