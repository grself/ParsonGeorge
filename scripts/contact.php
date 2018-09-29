<?php

// define variables and set to empty values
$name = $email = $comment = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  //echo "Proc 1001: POST Fired";
  $name = stripslashes($_POST["Input_Name"]);
  //echo "Proc 1002: Value of \$email: $email";
  $email = stripslashes($_POST["Input_Email"]);
  $comment = stripslashes($_POST["Input_Comment"]);
} else {
  $name = "Error 101: Server Error.";
  exit();
}

if(IsInjected($email))
{
  echo "Error 201: Bad email value!";
  exit;
}

$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
  'secret' => '6Leu2hsUAAAAAC4PG7OloaOHNGOAUOExlrQ8PEPe',
  'response' => $_POST["g-recaptcha-response"]
	);

$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);

$context  = stream_context_create($options);
$verify = file_get_contents($url, false, $context);
$captcha_success=json_decode($verify);

if ($captcha_success->success==false) {
  echo "<p>You are a bot! Go away!</p>";
  exit;
} else if ($captcha_success->success==true) {

  $email_from = 'parsongeorge@gmail.com';
  $email_subject = "Parson George Contact Form Submission";
  $email_body  = "You have a new message from: " . $name . ".\n\n";
  $email_body .= "Visitor's email: " . $email . ".\n\n";
  $email_body .= "Message: " . $comment;

  $email_to = "parsongeorge@gmail.com";
  $headers = "From: $email \r\n";
  $headers .= "Reply-To: $email \r\n";

  //Send the email!
  mail($email_to,$email_subject,$email_body,$headers);

  //done. redirect to thank-you page.
  header('Location: http://www.parsongeorge.com/pages/thankyou.html');
}

// Function to validate against any email injection attempts
// Note: HTML5 includes a validation feature for a form field
// with a type of "email." Thus, the following function should
// never actually fire since a bad email address cannot be 
// submitted. I checked the code in Chrome, Firefox, and Edge.
function IsInjected($str)
{
  // Look for these types of characters in the $email field since they 
  // are used to create a spoof email from my address.
  $injections = array('(\n+)',
              '(\r+)',
              '(\t+)',
              '(%0A+)',
              '(%0D+)',
              '(%08+)',
              '(%09+)'
              );
  // Build a regex match string
  $inject = join('|', $injections);
  $inject = "/$inject/i";
  // Execute the regex
  if(preg_match($inject,$str))
    {
    return true;
  }
  else
    {
    return false;
  }
}
   
?> 