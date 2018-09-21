<?php
global $wpdb;
require "class.biucg-queryhandler.php";

$query = new Biucg_Queryhandler($wpdb);

$state = $_POST['state'];
$type = $_POST['type'];
$file_name = (!empty($_POST['file_name'])) ? $_POST['file_name'] : "submission-".date('Ymd').".csv";

$result = $query->get_submission(array(
									'type' => $type,
									'state' => $state
								 ));


var_dump($result);
exit;



header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$file_name);

$output = fopen('php://output', 'w');

$header = array(
					'ID', 
					'User ID', 
					'User Name',
					'User E-Mail',
					'Content Type',
					'URL',
					'Meta Title',
					'Meta Image URL',
					'Meta Description',
					'Total Like',
					'Total View',
					'Status',
					'Submission Date'
				);

fputcsv($output,$header);
?>