<?php

date_default_timezone_set('Europe/Amsterdam');
setlocale(LC_ALL, 'nl_NL');
ini_set('memory_limit', '512M');
set_time_limit(0);
require_once 'config.php';
require_once 'configuration.php';
require_once 'lib/php/php-export-data.class.php';
	
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
			p.branch as version,
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
			p.branch as Version,
			p.submitted as 'Test time',
			m.act_id,
			m.choice,
			(m.stop_time - m.start_time) as response_time
		FROM
			personal_details p
		RIGHT JOIN
			measurements m
			ON m.subject_id = p.subject_id
		LEFT JOIN
			native_tongue n ON
			n.subject_id = p.subject_id
		WHERE
			p.submitted IS NOT NULL
		GROUP BY
			m.id
		ORDER BY
			m.subject_id ASC,
			m.start_time ASC");

	// Creating a workbook
	$export = new ExportDataExcel('browser', date('YmdHis') . '.xls');
	$export->initialize();

	// Header row
	$export->addRow(array('ID', 'Language', 'Age', 'Gender', 'Version',
		'Test time', 'Condition', 'Pronoun', 'Reaction time', 'Correct',
		'Item', 'Evaluation of mistakes',
		'Animal', 'Trail nr', 'Type nr'));

	$trails = array();
	$types = array();

	while ($data = $query->fetch(PDO::FETCH_ASSOC))
	{
		$col = 0;

		$row = array();

		// Skip rows that are not in the configuration file
		if (!isset($settings[$data['act_id']]))
			continue;

		list($num, $animal, $type) = explode('.', $settings[$data['act_id']]['audio_file_name']);

		if(!preg_match('/^(dir|ind|)(ik|jij|hij)$/', $type, $match))
			throw new Exception('Could not extract info from ' . $type);

		foreach (array('ID', 'Language', 'Age', 'Gender', 'Version', 'Test time') as $column)
			$row[$col++] = new ExportDataValue($data[$column], ExportDataValue::TYPE_STRING);
		
		$is_correct = $data['choice'] == $settings[$data['act_id']]['correct_position'];

		// Condition
		$row[$col++] = new ExportDataValue(ucfirst($match[1] ? $match[1] : 'no'), ExportDataValue::TYPE_STRING);
		
		// Pronoun
		$row[$col++] = new ExportDataValue(ucfirst($match[2]), ExportDataValue::TYPE_STRING);

		// Reaction time
		$row[$col++] = new ExportDataValue($data['response_time'], ExportDataValue::TYPE_NUMERIC);

		// Correct
		$row[$col++] = new ExportDataValue($is_correct, ExportDataValue::TYPE_NUMERIC);

		// Item
		$row[$col++] = new ExportDataValue($settings[$data['act_id']]['audio_file_name'], ExportDataValue::TYPE_STRING);

		// Alternative correct column
		if (!$is_correct)
		{
			switch ($match[1])
			{
				case 'dir':
					$alternative_choice = evaluation_of_mistakes_dir_ind($data['choice'], $settings[$data['act_id']]['correct_position'], $match[2]);
					$row[$col++] = new ExportDataValue($alternative_choice, ExportDataValue::TYPE_NUMERIC);
					break;

				case 'ind':
					$alternative_choice = !evaluation_of_mistakes_dir_ind($data['choice'], $settings[$data['act_id']]['correct_position'], $match[2]);
					$row[$col++] = new ExportDataValue($alternative_choice, ExportDataValue::TYPE_NUMERIC);
					break;

				default:
					$row[$col++] = null;
					break;
			}
		}
		else
			$row[$col++] = null; // Increment anyway

		// Animal
		$row[$col++] = $animal;

		// Trail number
		if (!isset($trails[$data['ID']]))
			$trails[$data['ID']] = 0;

		$row[$col++] = ++$trails[$data['ID']];

		// Seen of type
		if (!isset($types[$data['ID'] . $match[0]]))
			$types[$data['ID'] . $match[0]] = 0;

		$row[$col++] = ++$types[$data['ID'] . $match[0]];

		$export->addRow($row);
	}

	$export->finalize();
}

function evaluation_of_mistakes_dir_ind($choice, $correct, $pronoun)
{
	switch ($pronoun)
	{
		case 'hij':
			return $choice == 'option-1' && $correct == 'option-3'
				|| $choice == 'option-3' && $correct == 'option-1';

		case 'ik':
			return $choice == 'option-2' && $correct == 'option-1'
				|| $choice == 'option-2' && $correct == 'option-3';

		case 'jij':
			return $choice == 'option-1' && $correct == 'option-2'
				|| $choice == 'option-3' && $correct == 'option-2';
	}
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
	$export = new ExportDataExcel('browser', date('YmdHis') . '.xls');
	$export->initialize();

	$row = array();
		
	while ($data = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$data = $decorator->decorate($data);

		$col = 0;

		// If this is the first row, print the column headers
		if (!$printed_headers) {
			foreach (array_keys($data) as $column)
				$row[$col++] = new ExportDataValue($column, ExportDataValue::TYPE_STRING);
			$export->addRow($row);
			$printed_headers = true;
			$col = 0;
		}

		// Enclose text fields in quotes, and escape quotes in these fields.
		$numeric_colums = array('start_time', 'stop_time', 'difference');
		foreach ($data as $column => $field)
			$row[$col++] = new ExportDataValue($field,
				in_array($column, $numeric_colums) ? ExportDataValue::TYPE_NUMERIC : ExportDataValue::TYPE_STRING);

		$export->addRow($row);
	}

	$export->finalize();
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

	$db = config_pdo();

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
