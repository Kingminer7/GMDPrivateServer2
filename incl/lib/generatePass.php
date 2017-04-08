<?php
class generatePass
{
	public function isValidUsrname($userName, $pass) {
		include dirname(__FILE__)."/connection.php";
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$newtime = time() - (60*60);
		$query6 = $db->prepare("SELECT count(*) FROM actions WHERE type = '6' AND timestamp > :time AND value2 = :ip");
		$query6->execute([':time' => $newtime, ':ip' => $ip]);
		if($query6->fetchColumn() > 7){
			return 0;
		}else{
			$query = $db->prepare("SELECT accountID, salt, password, isAdmin FROM accounts WHERE userName = :userName");
			$query->execute([':userName' => $userName]);
			$result = $query->fetchAll();
			$result = $result[0];
			if(password_verify($pass, $result["password"])){
				if($result["isAdmin"]==1){ //modIPs
					$query4 = $db->prepare("SELECT count(*) FROM modips WHERE accountID = :id");
					$query4->execute([':id' => $result["accountID"]]);
					if ($query4->fetchColumn() > 0) {
						$query6 = $db->prepare("UPDATE modips SET IP=:hostname WHERE accountID=:id");
						$query6->execute([':hostname' => $ip, ':id' => $result["accountID"]]);
					}else{
						$query6 = $db->prepare("INSERT INTO modips (IP, accountID, isMod) VALUES (:hostname,:id,'1')");
						$query6->execute([':hostname' => $ip, ':id' => $result["accountID"]]);
					}
				}
				return 1;
			}else{
				$md5pass = md5($pass . "epithewoihewh577667675765768rhtre67hre687cvolton5gw6547h6we7h6wh");
				CRYPT_BLOWFISH or die ('-2');
				$Blowfish_Pre = '$2a$05$';
				$Blowfish_End = '$';
				$hashed_pass = crypt($md5pass, $Blowfish_Pre . $result['salt'] . $Blowfish_End);
				if ($hashed_pass == $result['password']) {
					$pass = password_hash($pass, PASSWORD_DEFAULT);
					//updating hash
					$query = $db->prepare("UPDATE accounts SET password=:password WHERE userName=:userName");
					$query->execute([':userName' => $userName, ':password' => $pass]);
					return 1;
				} else {
					if($md5pass == $result['password']){
						$pass = password_hash($pass, PASSWORD_DEFAULT);
						//updating hash
						$query = $db->prepare("UPDATE accounts SET password=:password WHERE userName=:userName");
						$query->execute([':userName' => $userName, ':password' => $pass]);
						return 1;
					} else {
						$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES 
																	('6',:username,:time,:ip)");
						$query6->execute([':username' => $userName, ':time' => time(), ':ip' => $ip]);
						return 0;
					}
				}
			}
		}
	}
	public function isValid($accid, $pass){
		include dirname(__FILE__)."/connection.php";
		$query = $db->prepare("SELECT userName FROM accounts WHERE accountID = :accid");
		$query->execute([':accid' => $accid]);
		$result = $query->fetchAll();
		$result = $result[0];
		$userName = $result["userName"];
		$generatePass = new generatePass();
		return $generatePass->isValidUsrname($userName, $pass);
	}
}
?>