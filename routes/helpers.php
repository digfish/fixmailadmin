<?php

use phpseclib\Net\SSH2;

function msg_error($msg_error)
{
    Log::error($msg_error);
    return response()->json(['error' => $msg_error]);
}

function tt($value)
{
    $d = new Dumper();
    $d->dump($value);
}

function engine()
{
    $db = DB::connection();
    return $db->getConfig('driver');
}

function passwd($str) {
	return crypt($str,'$1$rasmusle$');
}


function maildirmake($maildir) {
		if (!file_exists($maildir)) {
	    	$mkdir_success = mkdir($maildir,0777,true);
	        $mksubdir_sucess = create_maildir_subdirs($maildir);
    	    return $mkdir_success && $mksubdir_sucess;
		} else {
			return true;
		}
}

function create_maildir_subdirs($maildir) {
	$s1 = mkdir("$maildir/cur",0777,TRUE);
	$s2 = mkdir("$maildir/new",0777,TRUE);
	$s3 = mkdir("$maildir/tmp",0777,TRUE);

	return $s1 && $s2 && $s3;
}

function mysql_encrypt($password) {
	$row = mysql_execute("SELECT ENCRYPT($password) AS crypt");
	if ($row) {
		return $row->crypt;
	}
}


function mysql_execute($sql) {
	$mysql = DB::connection('mysql');
	$rows = $mysql->select($sql);
	if ( count($rows) > 0 ) {
		return $rows[0];
	} else {
		return NULL;
	}
}


function ssh_su_exec($command) {
	$ssh = new SSH2('localhost');
	$ssh->disableQuietMode();
	$ssh->login('root','rootpasswd');
	$output = $ssh->exec($command);
	$ssh->disconnect();
	return $output;
}

function su_exec($cmd) {
    $output = "''";
    $su_exec_binary = env('SU_EXEC_BINARY');
    //exec("/var/www/www-scripts/php-root $cmd", $output);
    exec("$su_exec_binary $cmd", $output);
    return $output;
}
