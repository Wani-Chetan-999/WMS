<?php 
// Include session and database connection
require_once '../controllerUserData.php';
include('database.inc');

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$email = $_SESSION['email'] ?? false;
$password = $_SESSION['password'] ?? false;

// Check if user is logged in
if (!$email || !$password) {
    echo "<script>alert('Please login first. Redirecting...'); window.location.href='../login-user.php';</script>";
    exit();
}

// Fetch user details
$sql = "SELECT * FROM usertable WHERE email = '$email'";
$run_Sql = mysqli_query($con, $sql);

if ($run_Sql) {
    $fetch_info = mysqli_fetch_assoc($run_Sql);
    $status = $fetch_info['status'];
    $code = $fetch_info['code'];

    if ($status !== "verified") {
        echo "<script>alert('Please verify your email. Redirecting...'); window.location.href='../user-otp.php';</script>";
        exit();
    }

    if ($code != 0) {
        echo "<script>alert('You need to reset your code. Redirecting...'); window.location.href='../reset-code.php';</script>";
        exit();
    }
}

$msg = "";
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $mobile = mysqli_real_escape_string($con, $_POST['mobile']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $location = mysqli_real_escape_string($con, $_POST['location']);    
    $locationdescription = mysqli_real_escape_string($con, $_POST['locationdescription']);
    $date = $_POST['date'];
    
    // Handling multiple checkbox values
    $checkbox1 = $_POST['wastetype'];  
    $chk = implode(",", $checkbox1);  

    // File upload handling
    $file = $_FILES['file']['name'];
    $target_dir = "upload/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $extensions_arr = array("jpg", "jpeg", "png", "gif", "tif", "tiff");

    if (in_array($imageFileType, $extensions_arr)) {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            echo "File uploaded successfully!";
        } else {
            echo "File upload failed!";
        }
    }

    // Insert into database
    $sql = "INSERT INTO garbageinfo (name, mobile, email, wastetype, location, locationdescription, file, date, status) 
            VALUES ('$name', '$mobile', '$email', '$chk', '$location', '$locationdescription', '$file', '$date', '$status')";

    if (mysqli_query($con, $sql)) {
        $msg = '<div class="alert alert-success">Complaint Registered Successfully! <a href="../adminlogin/welcome.php">View Complaint</a></div>';
    } else {
        $msg = '<div class="alert alert-warning">Failed to Register!</div>';
    }

    // Send Email Notification
    $html = "<table>
                <tr><td>Name: $name</td></tr>
                <tr><td>Mobile: $mobile</td></tr>
                <tr><td>Email: $email</td></tr>
                <tr><td>Waste Type: $chk</td></tr>
                <tr><td>Location: $location</td></tr>
                <tr><td>Description: $locationdescription</td></tr>
                <tr><td>Image: $file</td></tr>
                <tr><td>Date: $date</td></tr>
            </table>";

    require 'PHPMailerAutoload.php';
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->isHTML(true);
    $mail->Username = 'chetan.contact999@gmail.com';
    $mail->Password = 'abbjrykofsirvzcc'; // Use an App password
    $mail->setFrom('no-reply@yourdomain.com');     
    $mail->Subject = 'New Garbage Complaint Registered';
    $mail->Body = $html;     
    $mail->addAddress($email);
    
    if ($mail->send()) {
        echo "Email Sent Successfully!";
    } else {
        echo "Email Sending Failed!";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Bootstrap Select CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">

<!-- Bootstrap Select JS (after jQuery and Bootstrap's JS) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>

<link rel="stylesheet"type="text/css"href="style.css">
    <title>Complain</title>
  
</head>
<body>

	<div style="margin: 10px; padding: 10px;">
		<a href="/waste-management-system-main/index.html" class="fa fa-home" 
		style="font-size: 28px; color: white; padding: 8px 15px; background-color: transparent; text-decoration: none;">
			Home
		</a>
	</div>
   <?php 
   $error ='';   
   ?>
   <form method="post" action="trash.php" enctype="multipart/form-data">
   <div class="container">
	<div class="row">
		<div class="col-md-3">
			<div class="contact-info">
				<img src="images.jfif" alt="image"/>
				<h2>Register Your Complain</h2>
				<h4>We would love to hear from you !</h4>
			</div>
		</div>
		<div class="col-md-9">
			<div class="contact-form">
				<div class="form-group">
				<div id="error"></div>
              <span style="color:red"><?php echo "<b>$msg</b>"?></span>
				  <label class="control-label col-sm-2" for="fname"> Name:</label>
				  <div class="col-sm-10">          
					<input type="text" class="form-control" id="name" placeholder="Enter Your Name" name="name" required>
				  </div>
				</div>
				<div class="form-group">
				  <label class="control-label col-sm-2" for="lname">Mobile:</label>
				  <div class="col-sm-10">          
					<input type="number" class="form-control" id="mobile" placeholder="Enter Your Mobile Number" name="mobile"required min ="80000000" max="100000000000">
				  </div>
				</div>
				<div class="form-group">
				  <!-- <label class="control-label col-sm-2" for="email">Email:</label>
				  <div class="col-sm-10"> -->
					<input type="hidden" class="form-control" id="email" placeholder="Enter Your email" name="email" value="<?php echo   $_SESSION['email'];?>"> 
				  <!-- </div> -->
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2" for="option">Category:</label>
					<div class="col-sm-10">          
					    <input type="checkbox" name="wastetype[]" value="organic"> Organic
                        <input type="checkbox" name="wastetype[]" value="inorganic"> Inorganic
                        <input type="checkbox" name="wastetype[]" value="Household"> Household
                        <input type="checkbox" name="wastetype[]" value="mixed"id="mycheck" checked> All
					</div>
				  </div>
				  <div class="form-group">
					<label class="control-label col-sm-2" for="lname">Location:</label>
					<div class="col-sm-10">          
					   <select class="form-control" id="location"  data-live-search="true" name="location"required>
						   <option class="form-control" >Pune</option>
						   <option class="form-control" >Mumbai</option>
						   <option class="form-control" >Jalgaon</option>
						   <option class="form-control" >Nashik</option>
						   <option class="form-control" >Dhule</option>
					   </select>
					</div>
				  </div>
				<div class="form-group">
				  
				  <div class="col-sm-10">
					<textarea class="form-control" rows="5" id="locationdescription" placeholder="Enter Location details..." name="locationdescription" required></textarea>
				  </div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2" for="lname">Pictures:</label>
					<div class="col-sm-10">          
					  <input type="file" class="form-control" id="file" name="file"required accept="image/*" capture="camera">
					</div>
				  </div>
				<div class="form-group">        
				  <div class="col-sm-offset-2 col-sm-10">
				   <input type='hidden' class="form-control" id="date" name="status" value="Pending">
				    <input type="hidden" class="form-control" id="date" name="date" value="<?php $timezone = date_default_timezone_set("Asia/Kathmandu");
                                                                                             echo  date("g:ia ,\n l jS F Y");?>">
					<button type="submit" class="btn btn-default" name="submit" >Register</button>
				  </div>
				</div>
			</div>
		</div>
	</div>
</div>
   </form>
</div>
<script type="text/javascript"  src="formValidation.js"></script>
</body>

</html>
