<?php
   $firstName = $_POST['firstName'];
   $lastName = $_POST['lastName'];
   $email = $_POST['email'];
   $position = $_POST['position'];
   $school = $_POST['school'];
   $state = $_POST['state'];
   $message = $_POST['message'];


   $conn = new mysqli('localhost', 'root','root', 'contact', 8889);
   if ($conn->connect_error) {
       die('Connection Failed : '. $conn->connect_error);
   } else{
       $stmt = $conn->prepare("insert into contact(firstName, lastName, email, position, school, state, message) values(?, ?, ?, ?, ?, ?, ?)");
       $stmt->bind_param("sssssss", $firstName, $lastName, $email, $position, $school, $state, $message);
       $stmt->execute();
       echo "contact Successfully...";
       $stmt->close();
       $conn->close();
   }
?>
