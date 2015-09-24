<?php
$max_query_len = 2000;
$new_line = "\n";
$file_del = "\t";
$sort = 'desc';
$delimiter_for_query = 'duration: ';
$output_file = '';
$file_header = "";
$file_header .= "Time";
$file_header .= $file_del." Query";
$sql_where_operator = array('AND','OR','BETWEEN','IN');

$base_path = 'some/path';
$destination_folder = 'folder/where/output/file/to/be/written';

/**
 * $excluded_phrases contains the keywords/lines which we don't want to be reported in output report
 * @var unknown_type
 */
$excluded_phrases = array(
		'some_excluded_Words'
		);

		
$included_phrases = array('STATEMENT:');  //it is case insensitive		
		



//Headers to be appended in output.
//If column in not found, then it will simply skip that column
$output_file_columns = array('hash','group','query_count','time','query');

$files_read = array(
	'input/file/path1' => $destination_folder.'file1',
	'input/file/path2' => $destination_folder.'file2',
	'input/file/path3' => $destination_folder.'file3',
	);
	
include 'ignored_config.php';	