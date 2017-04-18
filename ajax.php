<?
require('config.php');
require('func.php');

$time = time()+60;
$sql->set("UPDATE writer SET writerTimeout = ? WHERE writerCookie = ?","si",array(date("Y-m-d H:i:s",$time),$_GET['writer']));
//setCookie('writer',$_GET['writer'],$time,'/');
$data = $sql->get("SELECT * FROM writer AS w,docs AS d WHERE w.writerCookie = ? AND docID = ?","si",array($_GET['writer'],$_GET['doc']));

if($data)
	$userID = $data[0]['writerID'];

if(isset($_GET['a'])) {
	$a = $_GET['a'];
}else{
	$a = '';
}

if($a == 'edit'){
	$line = $_GET['line'];
	$doc = $_GET['doc'];
	
	if($sql->set("UPDATE `lines` SET exclusive = ? WHERE docID = ? AND lineID = ?","iii", array($userID,$doc,$line))) {
		$last = $sql->set("INSERT INTO changes (docID,lineID,typ) VALUES (?,?,'block')","ii",array($doc,$line),true);
		echo 'true';
	}else{
		$data = $sql->get("SELECT exclusive FROM `lines` WHERE docID = ? AND lineID = ? AND exclusive = ?","iii",array($doc,$line,$userID));
		if($data){
			echo 'true';
		}else{
			echo 'false';
		}
	}
	//echo 'true';
}

if($a == 'add') {
	$line = $_GET['line'];
	$text = $_POST['text'];
	$doc = $_GET['doc'];
	
	$data = $sql->get("SELECT * FROM `lines` WHERE docID = ? AND lineID = ?","ii",array($doc,$line));
	
	if($data) {
		$dann = $sql->set("INSERT INTO `lines` (docID,content,danach) VALUES (?,?,?)","isi",array($doc,$text,$data[0]['danach']),true);
		
		$sql->set("UPDATE `lines` SET danach = ? WHERE lineID = ? AND docID = ?","iii",array($dann, $line, $doc));
		
		$last = $sql->set("INSERT INTO changes (docID,lineID,typ) VALUES (?,?,'add')","ii",array($doc,$line),true);
	
		$sql->set("UPDATE docs SET lastChange = ? WHERE docID = ?","ii",array($last,$doc));
		echo $dann;
	}
}

if($a == 'cat') {
	$doc = $_GET['doc'];
	$line = $_GET['line'];
	$text = $_POST['text'];
	
	$data = $sql->get("SELECT * FROM `lines` WHERE danach = ? AND docID = ?","ii",array($line,$doc));
	if(isset($data[0])) {
		if($data[0]['exclusive'] == '') {
			$content = $data[0]['content'] . $text;
			$sql->set("DELETE FROM `lines` WHERE docID = ? AND lineID = ?","ii",array($doc,$line));
			$sql->set("UPDATE `lines` SET content = ? WHERE docID = ? AND lineID = ?","sii",array($text,$doc,$data[0]['lineID']));
			echo json_encode(array('id'=>$data[0]['lineID'],'text'=>$content));
		}else{
			echo 'block';
		}
	}else{
		echo 'fail';
	}
}

//absatz zwischenspeichern
if($a == 'save'){
	$doc = $_GET['doc'];
	$line = $_GET['line'];
	$text = $_POST['text'];

	if(isset($_GET['b']) && $_GET['b'] == 'unblock') {
		$sql->set("UPDATE `lines` SET content = ?, exclusive = '' WHERE lineID = ? AND docID = ?","sii",array($text,$line,$doc));
		
		$sql->set("INSERT INTO changes (docID,lineID,typ) VALUES (?,?,'change')","ii",array($doc,$line));
		$last = $sql->set("INSERT INTO changes (docID,lineID,typ,data) VALUES (?,?,'unblock',?)","iis",array($doc,$line,$text),true);
	}else{
		$sql->set("UPDATE `lines` SET content = ? WHERE lineID = ? AND docID = ?","sii",array($text,$line,$doc));
	
		$last = $sql->set("INSERT INTO changes (docID,lineID,typ) VALUES (?,?,'change')","ii",array($doc,$line),true);
	}
	$sql->set("UPDATE docs SET lastChange = ? WHERE docID = ?","ii",array($last,$doc));
}

if($a == ''){
	$heartbeat = 30;
	$steps = 5;
	if(isset($_GET['last']) && $_GET['last'] > 0) {
		$last = $_GET['last'];
		$doc = $_GET['doc'];

		for($i=0;$i<$heartbeat;$i=$i+$steps) {
			if($data = $sql->get("SELECT lineID, typ, data ,changeID AS last FROM changes WHERE changeID > ? AND docID = ? ORDER BY changeID","ii",array($last,$doc))) {
				$out = array();
				foreach($data as $d) {
					$out[] = array('t'=>$d['typ'], 'l' => $d['lineID'], 'd' => $d['data']);
				}
				echo json_encode(array('data'=>$out,'last'=>$d['last']));
				die();
			}else{
				sleep($steps);
			}
		}

		echo json_encode(array('last'=>$_GET['last']));
	}else{
		//TODO irgendwie fehler
		echo json_encode(array('last'=>$_GET['last']));
	}
}
//TODO nicht eigene daten zusenden
?>