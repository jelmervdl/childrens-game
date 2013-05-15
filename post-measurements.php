<?php

class DataStore
{
	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function insertPersonalDetails($subject_id, $details)
	{
		$stmt = $this->db->prepare("INSERT INTO personal_details (subject_id, age, sex, browser, platform, submitted, branch) VALUES (:subject_id, :age, :sex, :browser, :platform, NOW(), :branch)");

		$stmt->bindParam(":subject_id", $subject_id);
		$stmt->bindParam(":age", $details->age);
		$stmt->bindParam(":sex", $details->sex);
		$stmt->bindParam(":browser", $details->browser);
		$stmt->bindParam(":platform", $details->platform);
		$stmt->bindValue(":branch", "koen");
		$stmt->execute();

		$stmt = $this->db->prepare("INSERT INTO native_tongue (subject_id, language) VALUES (:subject_id, :language)");

		$stmt->bindParam(":subject_id", $subject_id);
		$stmt->bindParam(":language", $language);

		foreach ($details->native_tongue as $language)
			$stmt->execute();
	}

	public function insertMeasurement($subject_id, $measurement)
	{
		$stmt = $this->db->prepare("INSERT INTO measurements (subject_id, act_id, start_time, stop_time, choice) VALUES (:subject_id, :act_id, :start_time, :stop_time, :choice)");

		$stmt->bindParam(":subject_id", $subject_id);
		$stmt->bindParam(":act_id", $measurement->id);
		$stmt->bindParam(":start_time", $measurement->startTime);
		$stmt->bindParam(":stop_time", $measurement->stopTime);
		$stmt->bindParam(":choice", $measurement->choice);

		$stmt->execute();
	}
}

$db = new PDO('mysql:host=127.0.0.1;dbname=franziska', 'franziska', 'franziska');

$data = json_decode(file_get_contents("php://input"));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$store = new DataStore($db);

try {
	foreach ($data as $trail)
	{
		$db->beginTransaction();

		$store->insertPersonalDetails($trail->subject_id, $trail->personal_data);

		foreach ($trail->measurements as $measurement)
			$store->insertMeasurement($trail->subject_id, $measurement);

		$db->commit();
	}
}
catch (Exception $e) {
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	echo $e;
}
