<html><head><style>
td,th {	width: 200px;}
.title {width: 400px;}
h3, form { text-align: center;}
.center {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 1000px;
}
</style></head><body>
<img src="SUmenubar.png" class="center"><hr>
<form action="sustudent.php" method="post">
<div class="form-group">
    <label for="studentd"><b>Query by Student ID #</b><label>
    <input type="text" class="form-control" name="studentid">
	<input type="submit" value="Submit">
</div></form>
<?php
$conn = new mysqli("localhost","root","","courseworkDB"); //MYSQLI connection variable.
if (isset($_POST['studentid'])) {  //Checks if anything was posted by the form.
	$sid = $_POST['studentid']; //Sets the student id.
}
else {
	$sid = ""; //Empties sid to prevent errors.
}
?>
<hr><h3>Personal Details</h3>
<table border=1 align="center">
<?php
//Retrieves everything from stud and appropriately concatenates.

$sdetails = "SELECT * FROM stud WHERE sid = '$sid'"; //Select query.
$result = $conn->query($sdetails); //Executes query
if ($result == TRUE) { 
		$row = $result->fetch_assoc() //Creates an associative array of the values from the query.
		$firstname = $row['firstname'];
		$surname = $row['lastname'];
		$title = $row['title'];
		$gender = $row['gender'];
		$dob = $row['dob'];
		//Converts m and f to appropriate counterparts.
		if ($gender == "m") {
			$gender = "Male";
		}
		else if ($gender == "f") {
			$gender = "Female";
		}
		//Creates the rest of the table.
		echo "<tr><th>Student ID</th><td>".$sid."</td></tr><tr><th>Title</th><td>".$title."</td></tr><tr><th>Full Name</th>
		<td>".$firstname." ".$surname."</th></tr><tr><th>Date Of Birth</th><td>".$dob."</td></tr><tr><th>Gender</th><td>"
		.$gender."</th></tr>";
}
?>
</table><h3>Course Details</h3>
<table border=1 align="center">
<?php
//Joins at the foreign keys and selects it where the ID in enrl matches the UCAS code.
$pdetails = "SELECT distinct prog.pid, prog.ptitle, prog.paward, prog.length, dept.dname 
			FROM prog
			INNER JOIN enrl ON enrl.pid = prog.pid
			INNER JOIN dept ON dept.did = prog.did
			WHERE enrl.sid = '$sid'";
$result = $conn->query($pdetails);

if ($result == TRUE) {
	    $row = $result->fetch_assoc();
		$pid = $row['pid'];
		$ptitle = $row['ptitle'];
		$paward = $row['paward'];
		$length = $row['length'];
		$dname = $row['dname'];
		//Similar principle as in personal details.
		echo "<tr><th>UCAS Code</th><td>".$pid."</td></tr><tr><th>Degree Scheme</th><td>"
			.$paward." ".$ptitle." ".$length."yr</td></tr><tr><th>Department</th><td>".$dname."</td></tr>";
}
?>
</table><h3>Enrolment and Progress</h3><table border=1 align="center">
<tr><th>Academic Year</th><th>Enrolment Status</th><th>Programme</th><th>Course Year</th></tr>
<?php
//Creates rows pertaining to each year of a person's time at University.
$edetails = "SELECT enrl.ayr, enrl.lvl, enrl.status, dept.dname
			FROM enrl
			INNER JOIN prog ON enrl.pid = prog.pid
			INNER JOIN dept ON dept.did = prog.did
			WHERE enrl.sid = '$sid'
			ORDER BY enrl.ayr Desc";
$result = $conn->query($edetails);
if ($result == TRUE) {
	while ($row = $result->fetch_assoc()) { //While loop allows for multiple rows to be created.
		$ayr = $row['ayr'];
		$level = $row['lvl'];
		$status = $row['status'];
		$dname = $row['dname'];		
		//Switch statement to adjust and convert the status.
		switch($status) {
			case "E":
				$status = "Enrolled";
				break;			
			case "NE":
				$status = "Not Enrolled";
				break;			
			case "ER":
				$status = "Enrolled Repeat";
				break;			
			case "W":
				$status = "Withdrawn";
				break;				
			case "S":
				$status = "Visiting";
				break;
			case "T":
			$status = "Transferred";
			break;
		}
		//Creates X rows for X entries in enrl.
		echo "<tr><td>".$ayr."</td><td>".$status."</td><td>".$dname."</td><td>".$level."</td></tr>";
	}
}
?>
</table><hr><h3>Module Selection</h3>
<?php
$ayrRetrieve = "SELECT DISTINCT ayr FROM smod WHERE sid = '$sid' order by ayr desc"; 
//Extracts the academic years relating to that student. Distinct is used in order to prevent duplicate tables.
$result = $conn->query($ayrRetrieve);
if ($result == TRUE) {
	//While loop used to create the possibility of multiple tables
	while ($value = $result->fetch_assoc()) {
		$ayr = $value['ayr']; //Takes the academic year and stores it in a variable for further use.
		$ayrModules = "SELECT mods.mtitle, mods.credits, mods.mid
					   FROM smod
					   INNER JOIN mods ON smod.mid = mods.mid
					   WHERE smod.sid = '$sid' AND smod.ayr = '$ayr'";
		
		$tabulator = $conn->query($ayrModules); //Runs the second query with Ayr as a WHERE value
		$sum_credits=0; //Initialises the credits variable.
		if ($tabulator == TRUE) {
			echo "<table border=1 align=center><tr align=center><th colspan=3>".$ayr."</th></tr>"; //Creates the table.
			while ($row = $tabulator->fetch_assoc()) {
				$mtitle = $row['mtitle'];
				$credits = $row['credits'];
				$mid = $row['mid'];
				$sum_credits=$sum_credits+$credits; //Increments the total credits.
				echo "<tr><td>".$mid."</td><td class=title>".$mtitle."</td><td>".$credits."</td></tr>"; //Creates rows
			}
			echo "<tr><td></td><td></td><td><b>Total Credits:</b>".$sum_credits."</td></tr>"; //Displays the total credits.
			echo "</table>"; //Closes the table.
		}
	}
}
?>
<img src="SUlogo.png" class="center">
</body>
</html>