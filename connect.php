<?php
// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   // Get form data
   $firstName = $_POST['firstName'];
   $lastName = $_POST['lastName'];
   $email = $_POST['email'];
   $position = $_POST['position'];
   $school = $_POST['school'];
   $state = $_POST['state'];
   $message = $_POST['message'];


   // Create connection
   $conn = new mysqli('localhost', 'root', 'root', 'contact', 8889);


   // Check connection
   if ($conn->connect_error) {
       die('Connection Failed: ' . $conn->connect_error);
   }


   // Prepare and bind statement to insert data into the database
   $stmt = $conn->prepare("INSERT INTO contact_us (firstName, lastName, email, position, school, state, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
   $stmt->bind_param("sssssss", $firstName, $lastName, $email, $position, $school, $state, $message);
  
   // Execute query and provide feedback
   if ($stmt->execute()) {
       echo "Contact successfully saved!";
   } else {
       echo "Error: " . $stmt->error;
   }


   // Close statement and connection
   $stmt->close();
   $conn->close();
} else {
   // If not a POST request
   echo "Invalid request method.";
}
?>

