<?
require('config.php');
require('func.php');

$time = time()+60;
$sql->set("UPDATE writer SET writerTimeout = ? WHERE writerCookie = ?","si",array(date("Y-m-d H:i:s",$time),$_COOKIE['writer']));
setCookie('writer',$_COOKIE['writer'],$time,'/');
$data = $sql->get("SELECT * FROM writer AS w,docs AS d WHERE w.writerCookie = ? AND docID = ?","si",array($_COOKIE['writer'],$_GET['doc']));

if(isset($_GET['a'])) {
	$a = $_GET['a'];
}else{
	$a = '';
}

if($a == 'edit'){
	$line = $_GET['line'];
	
	//absatz zum edit vormerken
	//echo 'false';
	echo 'true';
}

if($a == 'add') {
	$line = $_GET['line'];
	$text = $_GET['text'];
	
	$data = $sql->get("SELECT * FROM line WHERE docID = ? AND lineID = ?","ii",array($doc,$line));
	
	if($data) {
		$dann = $sql->set("INSERT INTO line (docID,content,danach) VALUES (?,?,?)","isi",array($doc,$text,$data[0]['danach']),true);
		
		$sql->set("UPDATE line SET danach = ? WHERE lineID = ? AND docID = ?","iii",array($dann,$line, $doc));
		
		$json = json_encode(array('t'=>'add','l'=>$dann,'d'=>$line));
		$last = $sql->set("INSERT INTO changes (docID,json) VALUES (?,?)","is",array($doc,$json),true);
	
		$sql->set("UPDATE docs SET lastChange = ? WHERE docID = ?","ii",array($last,$doc));
		echo $dann;
	}
}

//absatz zwischenspeichern
if($a == 'save'){
	$doc = $_GET['doc'];
	$line = $_GET['line'];
	$text = $_GET['text'];

	$sql->set("UPDATE line SET content = ? WHERE lineID = ? AND docID = ?","sii",array($text,$line,$doc));
	
	$json = json_encode(array('t'=>'save','l'=>$line));
	
	$last = $sql->set("INSERT INTO changes (docID,json) VALUES (?,?)","is",array($doc,$json),true);
	
	$sql->set("UPDATE docs SET lastChange = ? WHERE docID = ?","ii",array($last,$doc));
}

if($a == ''){
	$heartbeat = 30;
	$steps = 5;
	if(isset($_GET['last']) || $_GET['last'] == 0) {
		$last = $_GET['last'];

		for($i=0;$i<$heartbeat;$i=$i+$steps) {
			if(false) {//TODO change tab prüfen
				//TODO ausgabe
				die();
			}else{
				sleep($steps);
			}
		}

		echo json_encode(array('id'=>0,'type'=>'heartbeat','last'=>$_GET['last']));
	}else{
		echo json_encode(array('id'=>0,'type'=>'init','last'=>$_GET['last']));
	}
}

?>