<?php

use Illuminate\Http\Request;


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

Route::get('/get', function(Request $request) {
	$entity = $request->input('entity');
	$db = DB::connection();
	$rows = $db->select("SELECT * FROM $entity");
	$entity_fields = array();
	$row_buckets = array();

	if (count($rows) > 0) {
		$entity_fields = array_map(function($name) {
			return array('field' => $name);
		 } , array_keys(get_object_vars($rows[0])) );
	}

	foreach ($rows as $row) {
		$values = array();
		foreach ($row as $name => $value) {
			$values[] = array('value' => $value);
		}

		$row_buckets [] = array('values' =>$values);
	}

	return response()->json(
		array('fields' => $entity_fields,
			'rows' => $row_buckets
		)
	);
});

Route::get('/', function () {
	$db = DB::connection();
	$domains = $db->select('select * from domains');
	$users = $db->select('select * from users');
	$forwardings = $db->select('select * from forwardings');
	$transports = $db->select('select * from transport');

    return view('starter')
     ->with('domains',$domains)
     ->with('users',$users)
     ->with('forwardings',$forwardings)
     ->with('transports',$transports)
     ;
});

Route::get('test',function() {
	$test = array();
	$db = DB::connection();
	$test['domains'] = $db->select('select * from domains');
	$test['forwardings'] = $db->table('forwardings')->get();
	$test['transport'] = DB::table('transport')->get();
	$test['users'] = DB::table('users')->get();
	return view('test')->with('test',print_r($test,TRUE));
});

Route::get('add_row',function(Request $request) {
	$entity = $request->input('entity');
	$new_row = $request->input('row');
	Log::info($entity, array($new_row));
	$db = DB::connection();
	$columns_token = join(',',array_keys($new_row) );

	$values_token = join(',',
		array_map(function($value,$key) {
			if (!is_numeric($value)) { 
				$value = "'$value'";
			} else{
				$value = "$value";
			}
			

			if ($key == 'password') {
				$value = "ENCRYPT($value)";
			}
			return $value;
		},
		 array_values($new_row),
		 array_keys($new_row)
		)
	);

	$sql = "INSERT INTO $entity ($columns_token) VALUES ($values_token)";

	$db->insert($sql);
	Log::info('sql insert',array($sql));

	if ($entity == 'domains') {
		$domain = $new_row['domain'];
		$command_make_domain = "sudo -u www-data mkdir -p /home/vmail/$domain";
		Log::debug('command to make new domain dir:',array($command_make_domain));
		exec($command_make_domain,$output);
		Log::debug('make dir output:',array($output) );
	} else if ($entity == 'users') {
		$email =  $new_row['email'];
		$tokens = preg_split('/@/',$email);
		$username = $tokens[0];
		$domain = $tokens[1];
		$command_make_user = "sudo -u www-data maildirmake /home/vmail/$domain/$username";
		Log::debug('command to make new maildir:',array($command_make_user));
		$output="''";
		exec($command_make_user,$output);
		Log::info('make maildir output',array($output));
		exec("chown vmail:vmail /home/vmail/$domain/$username",$output);
		Log::info("chown",array($output));
	}
	return response()->json(['entity' => $entity, 'row' =>  $new_row]);
	//return response()->make($sql);
});

Route::get('delete_row',function(Request $request) {
	$row = $request->input('row');
	$entity = $request->input('entity');

	$key =  array_keys( $row )[0];
	$value = array_values($row)[0];

	if (!is_numeric($value)) {
		$value = "'$value'";
	}

	Log::debug('delete row: ', array($key,$value));
	$db = DB::connection();
	$sql= "DELETE FROM $entity WHERE $key=$value";
	Log::debug('SQL to delete:', array($sql));
	$ret = $db->delete($sql);
	return response()->json(['response'=>$ret]);
});


Route::get('update_row',function(Request $request) {
	$row = $request->input('row');
	$entity = $request->input('entity');

	$key =  array_keys( $row )[0];
	$key_value = array_values($row)[0];

	Log::debug('update row', array($entity,$row));

	$names = array_keys($row);
	$values = array_values($row);

	$pkvs = array();



    foreach ($row as $name => $val) {
		if ( ! is_numeric($val)) {
			$val = "'$val'";
		}
		$pkvs[] = "$name=$val";
		Log::debug('pkvs',array($pkvs));
	};

	$conc_pkvs = join(',',$pkvs);
	Log::debug('conc_pkvs',array($conc_pkvs));

	$db = DB::connection();
	$sql= "UPDATE $entity SET $conc_pkvs WHERE $key='$key_value'";
	Log::debug('SQL to update:', array($sql));

	$ret = $db->update($sql);
	return response()->json(['response'=>$ret]);


});
