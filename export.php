<?php

date_default_timezone_set('Europe/Amsterdam');
require_once 'configuration.php';

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
			p.subject_id,
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
			m.subject_id = p.subject_id");

	$decorator = new ConfigurationDecorator(get_configurations());

	print_results_as_csv($query, $decorator);
}

function export_to_excel(PDO $db)
{
	echo 'Sorry, not implemented yet. You can also use the r-data file, it works with Excel, but it is simply one big list.';
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
		<p>Export to <a href="export.php?target=r">R data</a> or <a href="export.php?target=excel">Excel</a></p>
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

	switch (@$_GET['target'])
	{
		case 'r':
			export_to_r($db);
			break;

		case 'excel':
			export_to_excel($db);
			break;

		default:
			print_index($db);
			break;
	}
}

main();