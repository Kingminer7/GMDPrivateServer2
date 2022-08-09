<?php
session_start();
require "../incl/dashboardLib.php";
$dl = new dashboardLib();
require "../../incl/lib/mainLib.php";
$gs = new mainLib();
/*
	generating packtable
*/
include "../../incl/lib/connection.php";
if(isset($_GET["page"]) AND is_numeric($_GET["page"]) AND $_GET["page"] > 0){
	$page = ($_GET["page"] - 1) * 10;
	$actualpage = $_GET["page"];
}else{
	$page = 0;
	$actualpage = 1;
}
$x = $page + 1;
$packtable = "";
$query = $db->prepare("SELECT levels,name,stars,coins FROM mappacks ORDER BY ID ASC LIMIT 10 OFFSET $page");
$query->execute();
$result = $query->fetchAll();
if(empty($result)) {
	$dl->printSong('<div class="form">
    <h1>'.$dl->getLocalizedString("errorGeneric").'</h1>
    <form class="form__inner" method="post" action="../dashboard">
		<p>'.$dl->getLocalizedString("emptyPage").'</p>
        <button type="submit" class="btn-primary">'.$dl->getLocalizedString("dashboard").'</button>
    </form>
</div>');
	die();
} 
foreach($result as &$pack){
	$lvlarray = explode(",", $pack["levels"]);
	$lvltable = "";
	foreach($lvlarray as &$lvl){
		$query = $db->prepare("SELECT levelID,levelName,starStars,userID,coins FROM levels WHERE levelID = :levelID");
		$query->execute([':levelID' => $lvl]);
		$level = $query->fetch();
		$lvltable .= "<tr>
						<td class='tcell'>".$level["levelID"]."</td>
						<td class='tcell'>".$level["levelName"]."</td>
						<td class='tcell'>".$gs->getUserName($level["userID"])."</td>
						<td class='tcell'>".$level["starStars"]."</td>
						<td class='tcell'>".$level["coins"]."</td>
					</tr>";
	}
	$packtable .= "<tr>
					<th scope='row'>$x</th>
					<td>".htmlspecialchars($pack["name"],ENT_QUOTES)."</td>
					<td>".$pack["stars"]."</td>
					<td>".$pack["coins"].'</td>
					<td><a class="dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							'.$dl->getLocalizedString("show").'
						</a>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink"  style="padding:17px 17px 0px 17px;">
							<table class="table" id="kek">
								<thead>
									<tr>
										<th class="tcell">'.$dl->getLocalizedString("ID").'</th>
										<th class="tcell">'.$dl->getLocalizedString("name").'</th>
										<th class="tcell">'.$dl->getLocalizedString("author").'</th>
										<th class="tcell">'.$dl->getLocalizedString("stars").'</th>
										<th class="tcell">'.$dl->getLocalizedString("userCoins").'</th>
									</tr>
								</thead>
								<tbody>
									'.$lvltable.'
								</tbody>
							</table>
						</div>
					</td>
					</tr>';
	$x++;
}
/*
	bottom row
*/
//getting count
$query = $db->prepare("SELECT count(*) FROM mappacks");
$query->execute();
$packcount = $query->fetchColumn();
$pagecount = ceil($packcount / 10);
$bottomrow = $dl->generateBottomRow($pagecount, $actualpage);
/* 
	printing
*/
$dl->printPage('<table class="table table-inverse">
  <thead>
    <tr>
      <th>#</th>
      <th>'.$dl->getLocalizedString("name").'</th>
      <th>'.$dl->getLocalizedString("stars").'</th>
      <th>'.$dl->getLocalizedString("coins").'</th>
	  <th>'.$dl->getLocalizedString("levels").'</th>
    </tr>
  </thead>
  <tbody>
    '.$packtable.'
  </tbody>
</table>'
.$bottomrow, true, "stats");
?>