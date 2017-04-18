<?php
	$DEBUG = true;

	class SQL {
		private $h;
		private $res = false;
		
		public function __construct($host,$user,$pass,$data) {
			$this->h = new mysqli($host, $user, $pass, $data);
			if ($this->h->connect_errno) {
				return false;
			}
			return true;
		}
		
		public function get($que,$t='',$p=array()) {
			try {
				$statement = $this->h->prepare($que);
				
				switch (count($p)) {
					case 0: break;
					case 1: $statement->bind_param($t, $p[0]); break;
					case 2: $statement->bind_param($t, $p[0], $p[1]); break;
					case 3: $statement->bind_param($t, $p[0], $p[1], $p[2]); break;
					case 4: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3]); break;
					case 5: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4]); break;
					case 6: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5]); break;
					case 7: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]); break;
					case 8: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7]); break;
					case 9: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8]); break;
					case 10: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9]); break;
				}
				
				$statement->execute();	

				$ret = array();
				
				$result = $statement->get_result();
				while($row = $result->fetch_assoc()) {
					$ret[] = $row;
				}
				
				if(count($ret) == 0) 
					return false;
				
				return $ret;
			} catch (Exception $e) {
				global $DEBUG;
				if($DEBUG) {
					echo mysqli_error($this->h) .'<br/>'. $que .'<br/>';
					print_r($p);
				}
			}
			return false;
		}
		
		public function set($que,$t='',$p=array(),$id=false) {
			$statement = $this->h->prepare($que);
			
			switch (count($p)) {
					case 0: break;
					case 1: $statement->bind_param($t, $p[0]); break;
					case 2: $statement->bind_param($t, $p[0], $p[1]); break;
					case 3: $statement->bind_param($t, $p[0], $p[1], $p[2]); break;
					case 4: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3]); break;
					case 5: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4]); break;
					case 6: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5]); break;
					case 7: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]); break;
					case 8: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7]); break;
					case 9: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8]); break;
					case 10: $statement->bind_param($t, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9]); break;
				}
			
			$statement->execute();
			if($id) {
				return $statement->insert_id;
			}else{
				return $statement->affected_rows;
			}
		}
		
		function __destruct() {
			$this->h->close();
			//echo 'DESTROY';
		}
	}
	
	function chk($str) {
		return str_replace("'",'"',$str);
	}

	function random($name_laenge) {
		$zeichen = "abcedfghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRTSUVWXYZ0123456789";
		$name_neu = "";

		@mt_srand ((double) microtime() * 1000000);
		for ($i = 0; $i < $name_laenge; $i++ ) {
			$r = mt_rand(0,strlen($zeichen)-1);
			$name_neu .= $zeichen{$r};
		}
		return $name_neu;
	}	
	
	$sql = new SQL($host,$user,$pass,$data);
?>