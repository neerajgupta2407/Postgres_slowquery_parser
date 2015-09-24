<?php 
ob_start();
include_once 'config.php';
include_once 'utils.php';


$filename = "data-".date('Y-m-d H:i:s').time().'.csv';

foreach ($files_read as $file => $name)
{
	$parse_file_output = parse_file($base_path.$file);

	download_file($parse_file_output, $name.'_All_Queries');
	
	$hashed_data = get_column_group_by($parse_file_output,'hash');
	$hashed_output = array();
	$i = 0;
	foreach ($hashed_data as $key => $val)
	{
		//selecting only the first occurence
		$hashed_output[$i] = $val[0];
		$hashed_output[$i]['query_count'] = count($val);
		
		$i++;
	}

	download_file($hashed_output, $name.'_unique_Queries');
}

?>