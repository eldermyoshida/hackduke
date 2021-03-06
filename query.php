<?php  
  
  	require_once ('db.php');

  // vars
  $queryType = @$_POST['Query'];

  // TestConnection: used by ppt plugin to see if can connect
  if ($queryType == 'TestConnection') {
    echo TRUE;
    exit(0);
  }

  // CreateQuestion: populates db with class info and sends texts/emails
  if($queryType == 'CreateQuestion') {
    $class_id = @$_POST['ClassId'];
	$teacher_name = @$_POST['TeacherName'];
	$teacher_email = @$_POST['TeacherEmail'];
	$teacher_password= @$_POST['TeacherPassword'];
	//Authenticate....
   	$quiz_title = @$_POST['Title'];
    $question = @$_POST['Question'];
        
	$success = TRUE;	
	$query = "INSERT INTO quiz (Title, Question, ClassId) VALUES ('{$quiz_title}', '{$question}', '{$class_id}')";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	$success = ($success && $result);
	$query = "DELETE FROM response WHERE ClassId=$class_id";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	$success = ($success && $result);
		echo $success;
	
	$result->free();	
	
	// CLOSE CONNECTION
	$mysqli->close();
	return;
  }
  
  //OpenQuiz
  if($queryType == 'OpenQuiz') {
	$class_id = @$_POST['ClassId'];
	$query = "SELECT class.StudentIds FROM hackdukedatabase.class WHERE ClassId='{$class_id}'";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		$row = mysqli_fetch_array($result, MYSQLI_NUM);
	} else {
		$result->free();
		// CLOSE CONNECTION
		$mysqli->close();
		exit(0);
		return;
	}
	
	$result->free();
	$subquery = "";
	
	$students = explode(',',$row[0]);
	foreach ($students as &$std) {
   		$subquery = $subquery."({$class_id}, {$std}),";
	}
	substr_replace($subquery ,"",-1); //remove extra comma

	$query = "INSERT INTO classlog (ClassId, StudentId) VALUES {$subquery}";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		$row = mysqli_fetch_array($result, MYSQLI_NUM);
	} else {
		$result->free();
		// CLOSE CONNECTION
		$mysqli->close();
		exit(0);
		return;
	}
	
	$result->free();
	
	// CLOSE CONNECTION
	$mysqli->close();
	return;
  }
  
  
  // GetResponse: returns comma separated student responses
  if ($queryType == 'GetResponse') {
	$classId = @$_POST['ClassId'];
	$query = "SELECT response.Response, response.StudentId FROM hackdukedatabase.response WHERE ClassId ='{$classId}' GROUP BY response.StudentId ORDER BY response.AddedOn DESC";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if(!is_null($result)){
		$rows = array();
		while (($row = mysqli_fetch_array($result, MYSQLI_NUM)) != NULL) {
			 array_push($rows, $row[1]);
			 array_push($rows, $row[0]);
		}
		$str = implode (",", $rows);
		echo $str;
		$result->free();
		// CLOSE CONNECTION
		$mysqli->close();
		exit(0);
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
  }

  // GetClasses: returns comma separated class ids from teacher name
  if ($queryType == 'GetClasses') {
	$teacher_name = @$_POST['TeacherName'];
	$query = "SELECT class.ClassId FROM hackdukedatabase.class WHERE TeacherName='{$teacher_name}'";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		$rows = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
			 array_push($rows, $row[0]);
		}
		$str = implode (",", $rows);
		echo $str;
		$result->free();
	 	// CLOSE CONNECTION
	 	$mysqli->close();
		exit(0);
		return;
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }

  // GetStudents: returns comma separated student ids given a class id
  if ($queryType == 'GetStudents') {
	$class_id = @$_POST['ClassId'];
	$query = "SELECT class.StudentIds FROM hackdukedatabase.class WHERE ClassId='{$class_id}'";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		$row = mysqli_fetch_array($result, MYSQLI_NUM);
		echo $row[0];
		$result->free();
	 	// CLOSE CONNECTION
	 	$mysqli->close();
		exit(0);
		return;
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }
  
  
  // GetStudents: returns comma separated student information given a class id
  if ($queryType == 'GetStudentsInfo') {	  
	   $class_id = @$_POST['ClassId'];
	$query = "SELECT class.StudentIds FROM hackdukedatabase.class WHERE ClassId='{$class_id}'";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		$row = mysqli_fetch_array($result, MYSQLI_NUM);
	} else {
		$result->free();
		// CLOSE CONNECTION
		$mysqli->close();
		exit(0);
		return;
	}
	   
	$result->free();	  
	$query = "SELECT student.StudentId, student.FirstName, student.LastName, student.PhoneNumber, student.Email FROM hackdukedatabase.student WHERE student.StudentId IN ({$row[0]})";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		$rows = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
			 array_push($rows, $row[0]);
			 array_push($rows, $row[1]);
		}
		$str = implode (",", $rows);
		echo $str;
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }
  if ($queryType == 'SetStudentsInfo') {	  
   $class_id = @$_POST['ClassId'];

	$j = 0;
	$post = implode(",", @$_POST['StudentsInfo']);
	for ($i = 0; $i < count($row); $i++)
	{
		$std_id = @$post[$i];
		$std_first_name =  @$post[$i + 1];
		$std_last_name =  @$post[$i + 2];
		$std_phone =  @$post[$i + 3];
		$std_email =  @$post[$i + 4];
		$j += 5;
		$query .= "UPDATE student.StudentId={$std_id}, student.FirstName={$std_first_name}, student.LastName={$std_last_name}, student.PhoneNumber={$std_phone}, student.Email={$std_email} FROM hackdukedatabase.student WHERE student.StudentId='{$std_id}'";
	}
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		$rows = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
			 array_push($rows, $row[0]);
			 array_push($rows, $row[1]);
		}
		$str = implode (",", $rows);
		echo $str;
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }
  
  if ($queryType == 'MakeStudent') {
	$std_first_name =  @$_POST['FirstName'];
	$std_last_name =  @$_POST['LastName'];
	$std_email =  @$_POST['Email'];
	$std_phone =  @$_POST['Phone'];
	$query = "INSERT INTO hackdukedatabase.student (FirstName, LastName, Email, PhoneNumber) VALUES ('{$std_first_name}', '{$std_last_name}', '{$std_email}', '{$std_phone}')";
   	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		echo 'True';
		$result->free();
	 	// CLOSE CONNECTION
	 	$mysqli->close();
		exit(0);
		return;
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }
  
   // SetStudents: takes in comma separated list of student ids and updates student list for given class id
  if ($queryType == 'SetStudents') {
	$class_id = @$_POST['ClassId'];
	$std_list = @$_POST['List'];
	$query = "UPDATE hackdukedatabase.class SET class.StudentIds='{$std_list}' WHERE ClassId='{$class_id}'";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		echo 'True';
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }

  // SetClasses: takes in comma separated list of class ids and reconciles classes in class table with list
  if ($queryType == 'SetClass') {
	$class_id = @$_POST['ClassId'];
	$teacher_name = @$_POST['TeacherName'];
	$teacher_email = @$_POST['TeacherEmail'];
	$teacher_password= @$_POST['TeacherPassword'];
	$query = "UPDATE hackdukedatabase.class SET TeacherName='{$teacher_name}', TeacherEmail='{$teacher_email}', TeacherPassword='{$teacher_password}' WHERE ClassId='{$class_id}'";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		echo 'True';
		$result->free();
	 	// CLOSE CONNECTION
	 	$mysqli->close();
		exit(0);
		return;
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }
  
  // MakeClass creates a class
  if ($queryType == 'MakeClass') {
	$class_id = @$_POST['ClassId'];
	$teacher_name = @$_POST['TeacherName'];
	$teacher_email = @$_POST['TeacherEmail'];
	$teacher_password= @$_POST['TeacherPassword'];
	$query = "INSERT INTO hackdukedatabase.class (TeacherName, TeacherEmail, TeacherPassword) VALUES ('{$teacher_name}', '{$teacher_email}', '{$teacher_password}')";
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result==TRUE){
		echo 'True';
		$result->free();
	 	// CLOSE CONNECTION
	 	$mysqli->close();
		exit(0);
		return;
	}
	   
	$result->free();
	// CLOSE CONNECTION
	$mysqli->close();
	exit(0);
	return;
  }
  
?>
