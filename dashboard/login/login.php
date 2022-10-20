<?php
session_start();
require "../incl/dashboardLib.php";
include "../".$dbPath."incl/lib/connection.php";
include "../".$dbPath."config/security.php";
$dl = new dashboardLib();
require "../".$dbPath."incl/lib/generatePass.php";
require_once "../".$dbPath."incl/lib/mainLib.php";
$gs = new mainLib();
$dl->title($dl->getLocalizedString("loginBox"));
$dl->printFooter('../');
if(isset($_SESSION["accountID"]) AND $_SESSION["accountID"] != 0){
	header('Location: ../');
	exit();
}
if(isset($_POST["userName"]) AND isset($_POST["password"])){
	$userName = $_POST["userName"];
	$password = $_POST["password"];
	$valid = GeneratePass::isValidUsrname($userName, $password);
	if($valid != 1){
		$dl->printSong('<div class="form">
		<h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
		<form class="field" action="" method="post">
		<p>'.$dl->getLocalizedString("wrongNickOrPass").'</p>
		<h2 style="margin-top:5px"><a href="login/activate.php">'.$dl->getLocalizedString("maybeActivate").'</a></h2>
		<button type="submit" class="btn btn-primary">'.$dl->getLocalizedString("tryAgainBTN").'</button>
		</form>
		</div>
		');
		exit();
	}
	$accountID = $gs->getAccountIDFromName($userName);
	if($accountID == 0){
		$dl->printLoginBoxError($dl->getLocalizedString("invalidid"));
		exit();
	}
	$_SESSION["accountID"] = $accountID;
	if(isset($_POST["ref"])){
		header('Location: ' . $_POST["ref"]);
	}elseif(isset($_SERVER["HTTP_REFERER"])){
		header('Location: ' . $_SERVER["HTTP_REFERER"]);
	}
	$dl->printLoginBox('<p>'.$dl->getLocalizedString("loginSuccess").'<button type="submit" class="btn-primary" >'.$dl->getLocalizedString("clickHere").'</button></p>');
}else{
	$loginbox = '<form class="field" action="" method="post">
							<div class="form-group">
								<input type="text" class="form-control login-input" id="p1" name="userName" placeholder="'.$dl->getLocalizedString("enterUsername").'">
							</div>
							<div class="form-group">
								<input type="password" class="form-control" id="p2" name="password" placeholder="'.$dl->getLocalizedString("enterPassword").'">
							</div>';
	if(isset($_SERVER["HTTP_REFERER"])){
		$loginbox .= '<input type="hidden" name="ref" value="'.$_SERVER["HTTP_REFERER"].'">';
	}
	$loginbox .= '<button type="submit" class="btn-primary btn-block" id="submit" disabled>'.$dl->getLocalizedString("login").'</button>
						</form>
<script>
$(document).change(function(){
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
</script>';
	$dl->printLoginBox($loginbox);
}
?>