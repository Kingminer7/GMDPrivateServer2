<?php
session_start();
require "../incl/dashboardLib.php";
require "../".$dbPath."incl/lib/connection.php";
$dl = new dashboardLib();
require_once "../".$dbPath."incl/lib/mainLib.php";
$gs = new mainLib();
require "../".$dbPath."incl/lib/exploitPatch.php";
require "../incl/XOR.php";
$xor = new XORCipher();
global $msgEnabled;
$dl->printFooter('../');
if(!isset($_POST["accountID"])) $_POST["accountID"] = 0; // cuz it sends warnings (it works! dont change anything pls ok thx)
if(!isset($_POST["receiver"])) $_POST["receiver"] = 0;
if($msgEnabled == 1) {
if(isset($_SESSION["accountID"]) AND $_SESSION["accountID"] != 0){
  	$newMsgs = $_SESSION["msgNew"];
	$accid = $_SESSION["accountID"];
	$notyou = ExploitPatch::number($_POST["accountID"]);
  	if(!isset($_GET["id"])) {
		if(empty($notyou)) {
			if(is_numeric($_POST["receiver"])) $notyou = ExploitPatch::number($_POST["receiver"]);
			else $notyou = $gs->getAccountIDFromName(ExploitPatch::remove($_POST["receiver"]));
		} 
	} else {
		$getID = explode("/", $_GET["id"])[count(explode("/", $_GET["id"]))-1];
		$notyou = ExploitPatch::remove($getID);
		if(is_numeric($notyou)) $notyou = ExploitPatch::number($notyou);
		else $notyou = $gs->getAccountIDFromName(ExploitPatch::remove($notyou));
	}
  	$check = $gs->getAccountName($notyou);
 	if(empty($check) OR $notyou == $accid) $dl->title($dl->getLocalizedString("messenger")); else $dl->title($dl->getLocalizedString("messenger").', '.$check);
	if(!empty($notyou) AND is_numeric($notyou) AND $notyou != 0 AND $notyou != $accid AND !empty($check)) {
		if(!empty($_POST["subject"]) AND !empty($_POST["msg"])) {
			$sendsub = base64_encode(ExploitPatch::remove($_POST["subject"]));
          	$query = $db->prepare("SELECT timestamp FROM messages WHERE accID=:accid AND toAccountID=:toaccid ORDER BY timestamp DESC LIMIT 1");
          	$query->execute([':accid' => $accid, ':toaccid' => $notyou]);
          	$res = $query->fetch();
          	$time = time() - 30;
          	if($res["timestamp"] > $time) {
     			   $dl->printSong('<div class="form">
            	        <h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
           	 	        <form class="form__inner" method="post" action="">
          		          <p>'.$dl->getLocalizedString("tooFast").'</p>
          		          <button type="submit" class="btn-primary" name="accountID" value="'.$notyou.'">'.$dl->getLocalizedString("tryAgainBTN").'</button>
  						  </form>
					</div>', 'msg');
              die();
            }
			$sendmsg = base64_encode($xor->cipher($_POST["msg"], 14251));
			$query = $db->prepare("INSERT INTO messages (userID, userName, body, subject, accID, toAccountID, timestamp, secret, isNew)
			VALUES (:uid, :nick, :body, :subject, :accid, :notyou, :time, 'Wmfd2893gb7', '0')");
			$query->execute([':uid' => $gs->getUserID($accid), ':nick' => $gs->getAccountName($accid), ':body' => $sendmsg, ':subject' => $sendsub, ':accid' => $accid, ':notyou' => $notyou, 'time' => time()]);
		}
		$query = $db->prepare("SELECT * FROM messages WHERE accID=:you AND toAccountID=:notyou OR accID=:notyou AND toAccountID=:you ORDER BY messageID DESC");
		$query->execute([':you' => $accid, ':notyou' => $notyou]);
		$res = $query->fetchAll();
		$msgs = '';
		foreach($res as $i => $msg) {
			if($msg["accID"] == $accid) { 
              $div = 'you';
              
            }
              else $div = 'notyou';
			$subject = base64_decode($msg["subject"]);
			$body = $xor->plaintext(base64_decode($msg["body"]), 14251);
			$msgs .= '<div class="messenger'.$div.'"><h2 class="subject'.$div.'">'.$subject.'</h2>
			<h3 class="message'.$div.'">'.$body.'</h3>
			<h3 id="comments" style="justify-content:flex-end">'.$dl->convertToDate($msg["timestamp"], true).'</h3></div>';
			$_POST["subject"] = '';
			$_POST["msg"] = '';
		}
		if(count($res) == 0) {
			$msgs .= '<div class="messenger"><p>'.$dl->getLocalizedString("noMsgs").'</p></div>';
		}
        $_SESSION["msgNew"] = $newMsgs = 0;
        $dl->printSong('<div class="form">
			<div style="display: inherit;align-items: center;margin: -5px;">
              <form method="post" action="profile/"><button class="goback" name="accountID" value="'.$notyou.'"><i class="fa-regular fa-user" aria-hidden="true"></i></button></form>
              <a class="a" href="messenger/"><h1>'.$gs->getAccountName($notyou).'</h1></a>
              <form method="post" action=""><button class="msgupd" name="accountID" value="'.$notyou.'"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></button></form>
            </div>
			<form class="form__inner dmbox" method="post" action="">'.$msgs.'</form>
			<form class="form__inner dmbox" method="post" action="">
				<div class="field"><input type="text" name="subject" id="p1" placeholder="'.$dl->getLocalizedString("subject").'"></input></div>
				<div class="field"><input type="text" name="msg" id="p2" placeholder="'.$dl->getLocalizedString("msg").'"></input></div>
			<button type="submit" name="accountID" value="'.$notyou.'" class="btn-primary btn-block" id="submit" disabled>'.$dl->getLocalizedString("send").'</button></form></div>
        <script>
$(document).on("keyup keypress change keydown",function(){
   const p1 = document.getElementById("p1");
   const p2 = document.getElementById("p2");
   const btn = document.getElementById("submit");
   if(!p1.value.trim().length || !p2.value.trim().length) {
                btn.disabled = true;
                btn.classList.add("btn-block");
                btn.classList.remove("btn-primary");
	} else {
		        btn.removeAttribute("disabled");
                btn.classList.remove("btn-block");
                btn.classList.remove("btn-size");
                btn.classList.add("btn-primary");
	}
});
			var notify = '.$newMsgs.';
            var elem = document.getElementById("notify");
            if(notify == 0) elem.parentNode.removeChild(elem);
        </script>', 'msg');
		$query = $db->prepare("UPDATE messages SET isNew=1 WHERE accID=:notyou AND toAccountID=:you");
		$query->execute([':you' => $accid, ':notyou' => $notyou]);
	} else {
		$query = $db->prepare("SELECT * FROM friendships WHERE person1=:acc OR person2=:acc");
		$query->execute([':acc' => $accid]);
		$result = $query->fetchAll();
		$options = '';
		foreach ($result as $i => $row) {
			if($row["person1"] == $accid) {
				$receiver = $gs->getAccountName($row["person2"]);
				$recid = $row["person2"];
			}
			else {
				$receiver = $gs->getAccountName($row["person1"]);
				$recid = $row["person1"];
			}
             $new = $db->prepare("SELECT count(isNew) FROM messages WHERE accID=:toid AND toAccountID=:id AND isNew=0");
          	$new->execute([':id' => $accid, ':toid' => $recid]);
          	$new2 = $new->fetchColumn();
          	$notify = '';
            if($new2 != 0) $notify = '<i class="fa fa-circle" aria-hidden="true" style="font-size: 10px;margin-left:5px;color: #e35151;"></i>';
			$options .= '<div class="messenger"><text class="receiver">'.$receiver.''.$notify.'</text><br>
			<a href="messenger/'.$gs->getAccountName($recid).'" class="btn-rendel" style="margin-top:5px;width:100%;display:inline-block">'.$dl->getLocalizedString("write").'</a></div>';
		}
		if(strpos($options, '<i class="fa fa-circle" aria-hidden="true" style="font-size: 10px;margin-left:5px;color: #e35151;"></i>') === FALSE AND $_SESSION["msgNew"] == 1) {
			$query = $db->prepare("SELECT accID FROM messages WHERE toAccountID=:acc AND isNew=0");
			$query->execute([':acc' => $accid]);
			$result = $query->fetchAll();
			foreach ($result as $i => $row) {
				$receiver = $gs->getAccountName($row["accID"]);
				$recid = $row["accID"];
				$notify = '<i class="fa fa-circle" aria-hidden="true" style="font-size: 10px;margin-left:5px;color: #e35151;"></i>';
				$options .= '<div class="messenger"><text class="receiver">'.$receiver.''.$notify.'</text><br>
				<a href="messenger/'.$gs->getAccountName($recid).'" class="btn-rendel" style="margin-top:5px;width:100%;display:inline-block">'.$dl->getLocalizedString("write").'</a></div>';
			}
		}
      	if(empty($options)) $options = '<div class="icon" style="height: 70px;width: 70px;margin-left: 0px;background:#36393e"><text class="receiver" style="font-size:50px"><i class="fa-regular fa-face-sad-cry"></i></text></div>';
		$dl->printSong('<div class="form">
			<h1>'.$dl->getLocalizedString("messenger").'</h1>
			<form class="form__inner" method="post" action="messenger/">
			<div class="msgbox" style="width:100%">'.$options.'</div></form>
            <form class="field" method="post" action="messenger/">
            <div class="messenger" style="width:100%"><input class="field" id="p1" type="text" name="receiver" placeholder="'.$dl->getLocalizedString("banUserID").'"></input>
            <button type="submit" class="btn-rendel btn-block" id="submit" style="margin-top:5px" disabled>'.$dl->getLocalizedString("write").'</button></div></form>
		<script>
$(document).on("keyup keypress change keydown",function(){
   const p1 = document.getElementById("p1");
   const btn = document.getElementById("submit");
   if(!p1.value.trim().length) {
                btn.disabled = true;
                btn.classList.add("btn-block");
                btn.classList.remove("btn-rendel");
	} else {
		        btn.removeAttribute("disabled");
                btn.classList.remove("btn-block");
                btn.classList.remove("btn-size");
                btn.classList.add("btn-rendel");
	}
});
			var notify = '.$newMsgs.';
            var elem = document.getElementById("notify");
            if(notify == 0) elem.parentNode.removeChild(elem);
        </script>', 'msg');
		}
} else {
  	$dl->title($dl->getLocalizedString("messenger"));
	$dl->printSong('<div class="form">
    <h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
    <form class="form__inner" method="post" action="./login/login.php">
	<p>'.$dl->getLocalizedString("noLogin?").'</p>
	        <button type="submit" class="btn-primary">'.$dl->getLocalizedString("LoginBtn").'</button>
    </form>
</div>', 'msg');
}
} else {
  		$dl->title($dl->getLocalizedString("messenger"));
		$dl->printSong('<div class="form">
			<h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
			<form class="form__inner" method="post" action=".">
			<p>'.$dl->getLocalizedString("pageDisabled").'</p>
			<button type="submit" class="btn-song">'.$dl->getLocalizedString("dashboard").'</button>
			</form>
		</div>');
}
?>
