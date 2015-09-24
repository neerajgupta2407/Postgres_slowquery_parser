<?php

function je($data)
{
	echo json_encode($data,True);

}

/**
 * Function checks if the current line follows all the rules
 * means: It should have any of the included phrases and it should not have any of the exluded phrases.
 * Priority will be given to included phrases.
 * If included phrase found, will return true
 * If excluded phrase found, WIll return false
 * If nothing found, return false.
 * @param unknown_type $line
 */
function filter_valid_line($line)
{
	global $excluded_phrases, $included_phrases;

	foreach ($excluded_phrases as $excluded_phrase)
	{
		if ( strpos(strtolower($line),strtolower($excluded_phrase)) > 0)
		{
			//yes...excluded phrase found..Hence returning false
			return false;
		}
	}

	foreach ($included_phrases as $included_phrase)
	{
		if ( strpos(strtolower($line),strtolower($included_phrase)) > 0)
		{
			//yes...excluded phrase found..Hence skiping the current line
			return true;
		}
	}



	//neither inclided not excluded phrase found..hence retuning false
	return false;

}


function parse_file($file_name)
{


	$myfile = fopen($file_name, "r") or die("Unable to open file!");

	while(!feof($myfile))
	{
		$line = fgets($myfile);

		//Checking if the current line contains the excluded phrases. if yes,
		// then we will not trace it
		if(!filter_valid_line($line))
		{
			//oops..the current line is not allowed to be in report..
			//hence continuing
			continue;
		}

		//echo $line;
		$res = get_time_n_query($line);
		//Trimming the query to 2000 char
		$output_arr[] = $res;

	}

	fclose($myfile);

	$res = sort_file($output_arr);
	return $res;

}

function get_time_n_query($line)
{

	include 'config.php';


	$pos_log = strpos($line, $delimiter_for_query);
	$pos_log  = $pos_log + strlen($delimiter_for_query);

	$trimmed_line   = substr($line, $pos_log);

	$time  = substr($trimmed_line,0, strpos($trimmed_line, ' '));

	//first occurence of select
	$query = substr($trimmed_line, strpos($trimmed_line, 'SELECT'));
	$query = substr($query, 0,$max_query_len);
	$sql_token = tokenize_query($query) ;
	$where_tokens = count_where_str_token($sql_token['where']);
	$assigned_grp = get_group_name($where_tokens);
	$hash = create_hash($sql_token['select'].$sql_token['table'].$assigned_grp);
	$response = array(
					'group' => $assigned_grp,
					'time' => $time,
					'hash' => $hash,
					 'query' => $query, 
					  'token' => $sql_token,
					'where_tokens' => $where_tokens,


	);
	return $response;

}


function create_group_wise_file($output)
{
	$t = time();
	foreach ($output as $key => $val)
	{
		$fp = fopen($key.'_'.$t.'.csv', 'w');
		foreach ( $val as $row)
		{
			$r = array();
			$r['group'] = $row['group'];
			$r['hash'] = $row['hash'];
			$r['time'] = $row['time'];
			$r['query'] = $row['query'];

			$output = '';
			fputcsv($fp, $r);
		}
		fclose($fp);
	}
}


function sort_file($data)
{

	foreach ($data as $key => $row)
	{
		$time[$key]  = $row['time'];
		$query[$key] = $row['query'];
	}
	array_multisort($time, SORT_DESC, $query, SORT_ASC, $data);
	return $data;

}

function tokenize_query($query)
{
	$select = get_string_between($query, "SELECT", "FROM");
	$table = get_string_between($query, "FROM", "WHERE");
	$where = get_string_between($query,"WHERE");

	return array('select' => $select,'table' => $table , 'where' => $where );

}

function get_string_between($string, $start, $end = '')
{
	$string = " ".$string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = $end != '' ? strpos($string,$end,$ini) - $ini : -1;
	return trim(substr($string,$ini,$len));
}

function download_file($output_arr, $filename = '')
{
	include 'config.php';
	/*
	 $output_str = '';
	 foreach ($output_arr as $key => $val)
	 {
		if($val['time'] === false or $val['query'] === false ) continue;
		$output_str .= $val['hash'].$file_del.$val['group'].$file_del.$val['time'].$file_del.'"'.$val['query'].'"'.$new_line;


		}

		if ($filename == '')
		{
		$file = 'Data_report';
		$filename = $file . "_" . date("Y-m-d_H-i", time());
		}

		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		header("Content-disposition: filename=" . $filename . ".csv");
		print $output_str;
		*/

	$fp = fopen($filename.'.csv', 'w');

	foreach ($output_arr as $fields)
	{
		$r = array();
		foreach ($output_file_columns as $columns)
		{
			if (array_key_exists($columns, $fields))
			{
				$r[$columns] = $fields[$columns];
			}
		}

		fputcsv($fp, $r);
	}

	fclose($fp);

}

/**
 * COunting the no of where operator in where line
 * Enter description here ...
 * @param unknown_type $where
 */
function count_where_str_token($where)
{
	global $sql_where_operator;
	$total_count = 0;
	$output = array();
	foreach ($sql_where_operator as $val)
	{
		$arr = explode($val, $where);
		$len = count($arr) - 1;
		$output[$val] = count($arr) - 1;
		$total_count  +=  $len;
	}

	$output['total'] = $total_count;

	return $output;
}

function get_group_name($where_tokens)
{
	$str = '';
	foreach ($where_tokens as $key => $val)
	{
		$str .= $key.'_'.$val.'_';
	}

	return substr($str,0,-1);
}

function create_hash($str)
{
	return md5($str);
}


function get_hash_count($output)
{
	$hash_arr = array();
	foreach ($output as $key => $val)
	{
		$hash = $val['hash'];
		$hash_arr[$hash] = $hash_arr[$hash] +1 ;
	}
	return $hash_arr;
}


function get_column_group_by($data,$column)
{
	if($column == '')
	{
		return 'Invalid Column';
	}

	$new_arr = array();
	foreach ($data as $val)
	{
		if ($val[$column] == '')
		{
			continue;
		}
		$new_arr[$val[$column]][] = $val;

	}

	return $new_arr;
}