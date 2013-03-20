<?php

$db = new PDO("mysql:host=localhost;dbname=gmod_ulx", "garrysmod", "password");

require_once "kvparser.php";

$kv = new KvParser();
$bans = $kv->GetArray("bans.txt");


foreach($bans as $steamId => $ban){
	
	if($ban['unban'] < time())
		continue;
		
	$adminName = '';
	$adminSteamId = '';
	
	if($ban['admin'] == '(Console)'){
		$adminName = '(Console)';
		
	} elseif(preg_match('#(.*?)\((STEAM_.*?)\)#', $ban['admin'], $matches)){
		$adminName = $matches[1];
		$adminSteamId = $matches[2];
	}
	
	$stmt = $db->prepare("insert into bans set OSteamID = ?, OName = ?, Length = ?, Time = ?, AName = ?, ASteamID = ?, Reason = ?, ServerID = 0, MAdmin = '', MTime = ?");
	$stmt->execute(array(
		$steamId,
		!empty($ban['name']) ? $ban['name'] : '',
		$ban['unban'],
		$ban['time'],
		$adminName,
		$adminSteamId,
		!empty($ban['reason']) ? $ban['reason'] : '',
		$ban['time'],
	));		
}