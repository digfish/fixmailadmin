<?php

use Illuminate\Http\Request;
use Illuminate\Support\Debug\Dumper;

include_once "helpers.php";

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/get', function (Request $request)
{
    $entity        = $request->input('entity');
    $db            = DB::connection();
    $rows          = $db->select("SELECT * FROM $entity");
    $entity_fields = array();
    $row_buckets   = array();

    if (count($rows) > 0)
    {
        $entity_fields = array_map(function ($name)
        {
            return array('field' => $name);
        }, array_keys(get_object_vars($rows[0])));
    }

    foreach ($rows as $row)
    {
        $values = array();
        foreach ($row as $name => $value)
        {
            $values[] = array('value' => $value);
        }

        $row_buckets[] = array('values' => $values);
    }

    return response()->json(
        array('fields' => $entity_fields,
            'rows'         => $row_buckets,
        )
    );
});

Route::get('/', function () 
{
    $db = DB::connection();

    $domains     = $db->select('select * from domains');
    $users       = $db->select('select * from users');
    $forwardings = $db->select('select * from forwardings');
    $transports  = $db->select('select * from transport');

    return view('master')
        ->with('domains', $domains)
        ->with('users', $users)
        ->with('forwardings', $forwardings)
        ->with('transports', $transports)
    ;
});

Route::get('test', function ()
{
    $test = array();
    $db   = DB::connection();

    $engine = $db->getConfig('driver');

    $test['engine'] = $engine;
//    $test['connection'] = tt($db);
    $test['domains']     = $db->select('select * from domains');
    $test['forwardings'] = $db->table('forwardings')->get();
    $test['transport']   = DB::table('transport')->get();
    $test['users']       = DB::table('users')->get();
    return view('test')->with('test', print_r($test, true));
});

Route::get('add_row', function (Request $request)
{
    $entity  = $request->input('entity');
    $new_row = $request->input('row');
    Log::info($entity, array($new_row));
    $vals = array_values($new_row);
    if (count($vals) == 1 && strlen(trim($vals[0])) == 0)
    {
        return msg_error("Can't crete empty $entity!");
    }

    Log::info('SQL engine',array(engine()));

    $db            = DB::connection();
    $columns_token = join(',', array_keys($new_row));

    $values_token = join(',',
        array_map(function ($value, $key)
        {
            if (!is_numeric($value))
            {
                $value = "'$value'";
            }
            else
            {
                $value = "$value";
            }

            if ($key == 'password')
            {
            	if (engine() == 'mysql') {
                	$value = "ENCRYPT($value)";
            	} else {
            		$encrypted =  mysql_encrypt($value);
                    Log::info("Using sqlite, need to obtain password hash from running mysql, obtained",[$encrypted]);
            		$value = "'$encrypted'";
            	}
            }
            return $value;
        },
            array_values($new_row),
            array_keys($new_row)
        )
    );

    $sql = "INSERT INTO $entity ($columns_token) VALUES ($values_token)";

    Log::info('sql insert', array($sql));

    DB::beginTransaction();
    try {
    	$db->insert($sql);
	} catch (Exception $ex) {
		msg_error($ex->getMessage());
	}


    // for 'domains' and 'users' we much make changes in the filesystem , here it is:

    if ($entity == 'domains') // create a new dir below /home/vmail for the new virtual domain
    {
        $domain              = $new_row['domain'];
        $new_dir_for_domain  = "/home/vmail/$domain";
        $command_make_domain = "mkdir -p $new_dir_for_domain";
        Log::debug('command to make new domain dir:', array($command_make_domain));
        exec($command_make_domain, $output);
        Log::debug('make dir output:', array($output));
        if (file_exists($new_dir_for_domain) === false)
        {
            DB::rollBack();
            return msg_error("Failed ! Couldn't create dir for $domain");

        }
    }
    else if ($entity == 'users') // create a maildir for the e-mail account
    {
        $email             = $new_row['email'];
        $tokens            = preg_split('/@/', $email);
        $username          = $tokens[0];
        $domain            = $tokens[1];
        $new_dir_for_user  = "/home/vmail/$domain/$username";

        // not using command maildirmake anymore, since php can do this
        $su_exec_binary = env('SU_EXEC_BINARY');
        $command_make_user = "$su_exec_binary maildirmake $new_dir_for_user";
        Log::debug('command to make new maildir:', array($command_make_user));
        $output = "''";
        exec($command_make_user, $output);
        Log::info('make maildir output', array($output));
       

        // maildirmake($new_dir_for_user); using PHP, not working on production !!!!!
        if (!file_exists($new_dir_for_user))
        {
            DB::rollback();
            return msg_error("Couldn't create maildir for email $email !");
        }



        $command_change_owner = "/bin/chown -Rv vmail:vmail $new_dir_for_user";

        $outcmd = su_exec($command_change_owner);

        Log::info("su_exec $command_change_owner => ",array($outcmd));

//        chown($new_dir_for_user,'vmail');
//        chgrp($new_dir_for_user,'vmail');

        $fileinfo = stat($new_dir_for_user);

        $owner = posix_getpwuid($fileinfo['uid'])['name'];
        $group = posix_getgrgid($fileinfo['gid'])['name'];

        Log::info("owner $new_dir_for_user", array($owner, $group));

        // we must ensure that the new maildir is properly owned by vmail !
        if ($owner != 'vmail' || $group != 'vmail')
        {
            DB::rollback();
            File::deleteDirectory($new_dir_for_user);
            return msg_error("Couldn't assign the owner and group correctly to new maildir!");
        }
    }
    DB::commit();
    return response()->json(['entity' => $entity, 'row' => $new_row]);
    //return response()->make($sql);
});


// TODO: does not make any changes to the filesystem
//
Route::get('delete_row', function (Request $request)
{
    $row    = $request->input('row');
    $entity = $request->input('entity');

    $key   = array_keys($row)[0];
    $value = array_values($row)[0];

    if (!is_numeric($value))
    {
        $value = "'$value'";
    }

    Log::debug('delete row: ', array($key, $value));
    $db  = DB::connection();
    $sql = "DELETE FROM $entity WHERE $key=$value";
    Log::debug('SQL to delete:', array($sql));
    $ret = $db->delete($sql);
    return response()->json(['response' => $ret]);
});

Route::get('update_row', function (Request $request)
{
    $row    = $request->input('row');
    $entity = $request->input('entity');

    $key       = array_keys($row)[0];
    $key_value = array_values($row)[0];

    Log::debug('update row', array($entity, $row));

    $names  = array_keys($row);
    $values = array_values($row);

    $pkvs = array();

    foreach ($row as $name => $val)
    {
        if (!is_numeric($val))
        {
            $val = "'$val'";
        }
        $pkvs[] = "$name=$val";
        Log::debug('pkvs', array($pkvs));
    };

    $conc_pkvs = join(',', $pkvs);
    Log::debug('conc_pkvs', array($conc_pkvs));

    $db  = DB::connection();
    $sql = "UPDATE $entity SET $conc_pkvs WHERE $key='$key_value'";
    Log::debug('SQL to update:', array($sql));

    $ret = $db->update($sql);
    return response()->json(['response' => $ret]);

});
