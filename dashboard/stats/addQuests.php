<?php
session_start();
include "../incl/dashboardLib.php";
require "../".$dbPath."incl/lib/Captcha.php";
include "../".$dbPath."incl/lib/connection.php";
require "../".$dbPath."incl/lib/generatePass.php";
require "../".$dbPath."incl/lib/exploitPatch.php";
require "../".$dbPath."incl/lib/mainLib.php";
$gs = new mainLib();
$dl = new dashboardLib();
$dl->title($dl->getLocalizedString("addQuest"));
$dl->printFooter('../');
if($gs->checkPermission($_SESSION["accountID"], "toolQuestsCreate")) {
if(!empty($_POST["type"]) AND !empty($_POST["amount"]) AND !empty($_POST["reward"]) AND !empty($_POST["names"])){
	if(!Captcha::validateCaptcha()) {
		$dl->printSong('<div class="form">
			<h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
			<form class="form__inner" method="post" action="">
			<p>'.$dl->getLocalizedString("invalidCaptcha").'</p>
			<button type="submit" class="btn-song">'.$dl->getLocalizedString("tryAgainBTN").'</button>
			</form>
		</div>', 'mod');
	die();
	}
	$type = ExploitPatch::number($_POST["type"]);
	$amount = ExploitPatch::number($_POST["amount"]);
    $reward = ExploitPatch::number($_POST["reward"]);
    $name = ExploitPatch::remove($_POST["names"]);
	$accountID = $_SESSION["accountID"];
		if(!is_numeric($type) OR !is_numeric($amount) OR !is_numeric($reward) OR $type > 3){
			$dl->printSong('<div class="form">
				<h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
				<form class="form__inner" method="post" action="">
				<p>'.$dl->getLocalizedString("invalidPost").'</p>
				<button type="submit" class="btn-primary">'.$dl->getLocalizedString("tryAgainBTN").'</button>
				</form>
			</div>', 'mod');
			die();
		}
		$query = $db->prepare("INSERT INTO quests (type, amount, reward, name) VALUES (:type,:amount,:reward,:name)");
		$query->execute([':type' => $type, ':amount' => $amount, ':reward' => $reward, ':name' => $name]);
		$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value2, value3, value4) VALUES ('25',:value,:timestamp,:account,:amount,:reward,:name)");
		$query->execute([':value' => $type, ':timestamp' => time(), ':account' => $accountID, ':amount' => $amount, ':reward' => $reward, ':name' => $name]);
		$success = $dl->getLocalizedString("questsSuccess").' <b>'. $name. '</b>!';
		if($db->lastInsertId() < 3) {
			$dl->printSong('<div class="form">
			<h1>'.$dl->getLocalizedString("addQuest").'</h1>
			<form class="form__inner" method="post" action="">
			<p>'.$success.'</p>
			<p>'.$dl->getLocalizedString("fewMoreQuests").'</p>
			<button type="submit" class="btn-primary">'.$dl->getLocalizedString("oneMoreQuest?").'</button>
			</form>
		</div>', 'mod');
		} else {
		$dl->printSong('<div class="form">
			<h1>'.$dl->getLocalizedString("addQuest").'</h1>
			<form class="form__inner" method="post" action="">
			<p>'.$success.'</p>
			<button type="submit" class="btn-primary">'.$dl->getLocalizedString("oneMoreQuest?").'</button>
			</form>
		</div>', 'mod');
		}
	} else {
		$dl->printSong('<div class="form">
    <h1>'.$dl->getLocalizedString("addQuest").'</h1>
    <form class="form__inner" method="post" action="">
	<p>'.$dl->getLocalizedString("addQuestDesc").'</p>
	 <div class="field" id="selecthihi">
	 <input class="quest" type="text" name="names" id="p1" placeholder="'.$dl->getLocalizedString("questName").'"></div>
	 <div class="field" id="selecthihi">
		<select name="type">
			<option value="1">'.$dl->getLocalizedString("orbs").'</option>
			<option value="2">'.$dl->getLocalizedString("coins").'</option>
			<option value="3">'.$dl->getLocalizedString("stars").'</option>
		</select></div>
        <div class="field" id="selecthihi">
		<input class="number" type="number" name="amount" id="p2" placeholder="'.$dl->getLocalizedString("questAmount").'">
		<input class="number" type="number" name="reward" id="p3" placeholder="'.$dl->getLocalizedString("questReward").'">
		</div>
		', 'mod');
		Captcha::displayCaptcha();
        echo '
        <button  type="submit" class="btn-song btn-block" id="submit" disabled>'.$dl->getLocalizedString("questCreate").'</button>
    </form>
</div>
<script>
$(document).change(function(){
   const p1 = document.getElementById("p1");
   const p2 = document.getElementById("p2");
   const p3 = document.getElementById("p3");
   const btn = document.getElementById("submit");
   if(!p1.value.trim().length || !p2.value.trim().length || !p3.value.trim().length) {
                btn.disabled = true;
                btn.classList.add("btn-block");
                btn.classList.remove("btn-song");
	} else {
		        btn.removeAttribute("disabled");
                btn.classList.remove("btn-block");
                btn.classList.remove("btn-size");
                btn.classList.add("btn-song");
	}
});
</script>';
}
} else {
	$dl->printSong('<div class="form">
    <h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
    <form class="form__inner" method="post" action=".">
		<p>'.$dl->getLocalizedString("noPermission").'</p>
	        <button type="submit" class="btn-primary">'.$dl->getLocalizedString("Kish!").'</button>
    </form>
</div>', 'mod');
}
?>
