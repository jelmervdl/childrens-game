<?php

date_default_timezone_set('Europe/Amsterdam');
setlocale(LC_ALL, 'nl_NL');
ini_set('memory_limit', '512M');
require_once 'configuration.php';
require_once 'lib/php/PHPExcel.php';
require_once 'lib/php/PHPExcel/Writer/Excel2007.php';
	
interface Decorator
{
	public function decorate(array $row);
}

class DummyDecorator implements Decorator
{
	public function decorate(array $row)
	{
		return $row;
	}
}

class ConfigurationDecorator implements Decorator
{
	public function __construct(array $config)
	{
		$this->configurations = array();

		// Copy all configurations to a flat version
		foreach ($config as $section => $configurations)
		{
			foreach ($configurations as $configuration)
			{
				$configuration['section'] = $section;
				$configuration['sentence'] = trim(@iconv('UTF-8', 'ASCII//TRANSLIT', $configuration['sentence']));
				$this->configurations[$configuration['code']] = $configuration;
			}
		}
	}

	public function decorate(array $row)
	{
		$configuration = $this->configurations[$row['act_id']];

		$configuration['correct'] = $row['choice'] == $configuration['correct_position'] ? '1' : '0';

		return array_merge($row, $configuration);
	}
}

function export_to_r(PDO $db)
{
	$query = $db->query("
		SELECT
			CONCAT('p', p.subject_id) as subject_id,
			p.age,
			p.sex,
			p.browser,
			p.platform,
			p.submitted,
			n.language,
			m.id as measurement_id,
			m.act_id,
			m.choice,
			m.start_time,
			m.stop_time,
			(m.stop_time - m.start_time) as difference
		FROM
			personal_details p
		LEFT JOIN
			native_tongue n ON
			n.subject_id = p.subject_id
		RIGHT JOIN
			measurements m ON
			m.subject_id = p.subject_id
		WHERE
			p.submitted IS NOT NULL");

	return $query;
}

function export_to_excel(PDO $db)
{
	$configurations = get_configurations();

	$columns = array();
	
	foreach (array_merge($configurations['no_report_items'], $configurations['direct_and_indirect_speech_items']) as $configuration)
		$columns[$configuration['code']] = $configuration;

	$query = $db->query("
		SELECT
			CONCAT('p', p.subject_id) as subject_id,
			p.submitted,
			m.act_id,
			m.choice,
			(m.stop_time - m.start_time) as response_time
		FROM
			personal_details p
		RIGHT JOIN
			measurements m
			ON m.subject_id = p.subject_id
		ORDER BY
			p.submitted ASC,
			p.subject_id ASC");

	uasort($columns, function($a, $b) {
		return strcmp($a['audio_file_name'], $b['audio_file_name']);
	});

	$now = date('YmdHis');

	header("Content-Type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename={$now}.xls");  //File name extension was wrong
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	ob_start('ob_gzhandler');

	echo '<table>';

	// Header row 1
	echo '<tr><th scope="col" rowspan="2">Participants</th>';

	foreach ($columns as $column)
		printf('<th scope="col" colspan="2">%s</th>', $column['audio_file_name']);

	echo '</tr>';

	// Header row 2
	echo '<tr>';

	for ($i = 0; $i < count($columns); ++$i)
		echo '<th scole="col">RT</th><th scole="col">Correct</th>';

	echo '</tr>';

	$subject_id = null;
	$subject_index = 0;
	$stats = array();

	while (true)
	{
		$row = $query->fetch(PDO::FETCH_ASSOC);

		if (!$row || $subject_id != $row['subject_id'])
		{
			// Print the collected columns
			if ($subject_id != null)
			{
				foreach ($columns as $act_id => $column)
					printf('<td>%d</td><td>%d</td>',
						@$stats[$act_id]['response_time'],
						@$stats[$act_id]['choice'] == $column['correct_position']);
				
				echo '</tr>';
			}

			if (!$row)
				break;
		
			echo '<tr>';
			printf('<th scope="row">%d%s</th>',
				++$subject_index,
				$row['submitted'] == '' ? '*' : '');
			$subject_id = $row['subject_id'];
			$stats = array();
		}

		$stats[$row['act_id']] = $row;
	}
}

function export_to_excel_complex(PDO $db)
{
	$configurations = get_configurations();

	$settings = array();
	
	foreach (array_merge($configurations['no_report_items'], $configurations['direct_and_indirect_speech_items']) as $configuration)
		$settings[$configuration['code']] = $configuration;

	$query = $db->query("
		SELECT
			CONCAT('p', p.subject_id) as ID,
			GROUP_CONCAT(n.language) as Language,
			p.age as Age,
			p.sex as Gender,
			p.submitted as 'Test time',
			m.act_id,
			m.choice,
			(m.stop_time - m.start_time) as response_time
		FROM
			personal_details p
		RIGHT JOIN
			measurements m
			ON m.subject_id = p.subject_id
		WHERE
			p.submitted IS NOT NULL
		GROUP BY
			p.subject_id,
			p.age,
			p.sex,
			p.submitted
		ORDER BY
			m.act_id ASC,
			p.submitted ASC");

	// Creating a workbook
	$workbook = new PHPExcel();
	$workbook->setActiveSheetIndex(0);

	$now = date('YmdHis');

	$worksheet = $workbook->getActiveSheet();
	$worksheet->setTitle('Exported data');
	
	$row = 1;
	$col = 0;

	// Header row
	foreach (array('ID', 'Language', 'Age', 'Gender', 'Test time', 'Condition', 'Pronoun', 'Reaction time', 'Correct', 'Item') as $column)
		$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $column, PHPExcel_Cell_DataType::TYPE_STRING);

	for ($i = 0;;++$i)
	{
		$row = $i + 2;
		$col = 0;

		$data = $query->fetch(PDO::FETCH_ASSOC);

		if (!$data)
			break;

		foreach (array('ID', 'Language', 'Age', 'Gender', 'Test time') as $column)
			$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $data[$column],
					$column == 'Age' ? PHPExcel_Cell_DataType::TYPE_NUMERIC : PHPExcel_Cell_DataType::TYPE_STRING);
		
		// Subject id
		$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $data['subject_id'], PHPExcel_Cell_DataType::TYPE_STRING);

		// Skip rows that are not in the configuration file
		if (!isset($settings[$data['act_id']]))
			continue;

		if(!preg_match('/\.(dir|ind|)(ik|jij|hij)$/', $settings[$data['act_id']]['audio_file_name'], $match))
			throw new Exception('Could not extract info from ' . $settings[$data['act_id']]['audio_file_name']);

		// Condition
		$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, ucfirst($match[1] ? $match['1'] : 'no'), PHPExcel_Cell_DataType::TYPE_STRING);
		
		// Pronoun
		$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, ucfirst($match[2]), PHPExcel_Cell_DataType::TYPE_STRING);

		// Reaction time
		$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $data['response_time'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

		// Correct
		$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $data['choice'] == $settings[$data['act_id']]['correct_position'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

		// Item
		$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $settings[$data['act_id']]['audio_file_name'], PHPExcel_Cell_DataType::TYPE_STRING);
	}

	$writer = new PHPExcel_Writer_Excel2007($workbook);
	
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Content-type: application/vnd.ms-excel;charset=UTF-8"); 
	header("Content-Disposition: attachment; filename=\"{$now}.xls\"");
	header("Cache-control: private");

	$writer->save('php://output');
}

function print_results_as_csv(PDOStatement $stmt, Decorator $decorator = null)
{
	$now = date('YmdHis');

	header("Content-Type: application/octet-stream");
	header("Content-Transfer-Encoding: Binary");
	header("Content-disposition: attachment; filename=\"{$now}.csv\"");
	
	ob_start("ob_gzhandler");
	
	$printed_headers = false;

	if (!$decorator)
		$decorator = new DummyDecorator();

	while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$row = $decorator->decorate($row);

		// If this is the first row, print the column headers
		if (!$printed_headers) {
			echo implode(",", array_keys($row)) . "\n";
			$printed_headers = true;
		}

		// Enclose text fields in quotes, and escape quotes in these fields.
		foreach (array('browser', 'platform', 'language', 'submitted') as $field)
			$row[$field] = sprintf('"%s"', str_replace('"', '""', $row[$field]));

		// print the row.
		echo implode(",", $row) . "\n";
	}
}

function print_results_as_excel(PDOStatement $stmt, Decorator $decorator = null)
{
	$now = date('YmdHis');

	$printed_headers = false;

	if (!$decorator)
		$decorator = new DummyDecorator();

	// Creating a workbook
	$workbook = new PHPExcel();
	$workbook->setActiveSheetIndex(0);

	$now = date('YmdHis');

	$worksheet = $workbook->getActiveSheet();
	$worksheet->setTitle('Exported data');
	
	for ($row = 1, $col = 0; $data = $stmt->fetch(PDO::FETCH_ASSOC); ++$row, $col = 0)
	{
		$data = $decorator->decorate($data);
		
		// If this is the first row, print the column headers
		if (!$printed_headers) {
			foreach (array_keys($data) as $column)
				$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $column, PHPExcel_Cell_DataType::TYPE_STRING);
			$printed_headers = true;
			$row++;
			$col = 0;
		}

		// Enclose text fields in quotes, and escape quotes in these fields.
		$numeric_colums = array('start_time', 'stop_time', 'difference');
		foreach ($data as $column => $field)
			$worksheet->setCellValueExplicitByColumnAndRow($col++, $row, $field,
				in_array($column, $numeric_colums) ? PHPExcel_Cell_DataType::TYPE_NUMERIC : PHPExcel_Cell_DataType::TYPE_STRING);
	}

	$writer = new PHPExcel_Writer_Excel2007($workbook);
	
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Content-type: application/vnd.ms-excel;charset=UTF-8"); 
	header("Content-Disposition: attachment; filename=\"{$now}.xls\"");
	header("Cache-control: private");

	$writer->save('php://output');
}


function fetch_one(PDOStatement $stmt)
{
	$values = $stmt->fetch(PDO::FETCH_NUM);

	return $values ? $values[0] : null;
}

function print_index(PDO $db)
{
	$last_submission = fetch_one($db->query("SELECT MAX(submitted) as last_submission FROM personal_details"));

	$number_of_submissions = fetch_one($db->query("SELECT COUNT(subject_id) FROM personal_details"));
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Export</title>
		<style>
			body {
				font: 14px sans-serif;
			}
		</style>
	</head>
	<body>
		<p>Submissions: <strong><?=$number_of_submissions?></strong></p>
		<p>Last submission: <strong><?=$last_submission?></strong></p>
		<p>Export to:</p>
		<ul>
			<li><a href="export.php?target=r-csv">Raw data CSV</a></li>
			<li><a href="export.php?target=r-excel">Raw data Excel</a> (may be slow)</li>
			<li><a href="export.php?target=excel">Simple excel sheet</a></li>
			<li><a href="export.php?target=excel_complex">Complex excel sheet</a> (may be a bit slow)</li>
		</ul>
	</body>
</html>
<?php
}

function main()
{
	error_reporting(E_ALL);
	ini_set('display_errors', true);
	ini_set('auto_detect_line_endings', true);

	$db = new PDO('mysql:host=127.0.0.1;dbname=franziska', 'franziska', 'franziska');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	switch (@$_GET['target'])
	{
		case 'r-csv':
			print_results_as_csv(export_to_r($db), new ConfigurationDecorator(get_configurations()));
			break;

		case 'r-excel':
			print_results_as_excel(export_to_r($db), new ConfigurationDecorator(get_configurations()));
			break;

		case 'excel':
			export_to_excel($db);
			break;
		
		case 'excel_complex':
			export_to_excel_complex($db);
			break;

		default:
			print_index($db);
			break;
	}
}

main();
