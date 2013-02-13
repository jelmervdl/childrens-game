<?php

class DataStore
{
	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function insertPersonalDetails($subject_id, $details)
	{
		$stmt = $this->db->prepare("INSERT INTO personal_details (subject_id, age, sex) VALUES (:subject_id, :age, :sex)");

		$stmt->bindParam(":subject_id", $subject_id);
		$stmt->bindParam(":age", $details->age);
		$stmt->bindParam(":sex", $details->sex);
		$stmt->execute();

		$stmt = $this->db->prepare("INSERT INTO native_tongue (subject_id, language) VALUES (:subject_id, :language)");

		$stmt->bindParam(":subject_id", $subject_id);
		$stmt->bindParam(":language", $language);

		foreach ($details['native_tongue'] as $language)
			$stmt->commit();
	}

	public function insertMeasurement($subject_id, $measurement)
	{
		$stmt = $this->db->prepare("INSERT INTO measurements (subject_id, act_id, start_time, end_time, choice) VALUES (:subject_id, :act_id, :start_time, :end_time, :choice)");

		$stmt->bindParam(":subject_id", $subject_id);
		$stmt->bindParam(":act_id", $measurement->act_id);
		$stmt->bindParam(":start_time", $measurement->start_time);
		$stmt->bindParam(":end_time", $measurement->end_time);
		$stmt->bindParam(":choice", $measurement->choice);

		$stmt->execute();
	}
}

$db = new PDO('mysql:host=127.0.0.1;dbname=franziska', 'franziska', 'franziska');

$data = json_decode(file_get_contents("php://input"));

$store = new DataStore($db);

foreach ($data as $trail)
{
	$store->insertPersonalDetails($trail->subject_id, $trail->personal_data);

	foreach ($trail->measurements as $measurement)
		$store->insertMeasurement($trail->subject_id, $measurement);

	$store->commit();
}
