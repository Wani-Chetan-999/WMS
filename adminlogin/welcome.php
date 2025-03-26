<?php 
require_once '../controllerUserData.php';
$email = $_SESSION['email'] ?? '';
$password = $_SESSION['password'] ?? '';

if (!empty($email) && !empty($password)) {
    $sql = "SELECT * FROM usertable WHERE email = '$email'";
    $run_Sql = mysqli_query($con, $sql);

    if ($run_Sql) {
        $fetch_info = mysqli_fetch_assoc($run_Sql);
        $status = $fetch_info['status'];
        $code = $fetch_info['code'];

        if ($status == "verified") {
            if ($code != 0) {
                header('Location: ../reset-code.php');
                exit();
            }
        } else {
            header('Location: ../user-otp.php');
            exit();
        }
    }
} else {
    header('Location: ../login-user.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            font-family: "Lato", sans-serif;
        }

        .sidebar {
            margin: 0;
            padding: 0;
            width: 150px;
            background-color: #37517e;
            position: fixed;
            height: 100%;
            overflow: auto;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 16px;
            text-decoration: none;
        }

        .sidebar a:hover {
            color: whitesmoke;
            text-decoration: none;
        }

        .logo1 {
            border-radius: 50%;
        }

        @media screen and (max-width: 700px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .sidebar a {
                float: left;
            }
        }

        @media screen and (max-width: 400px) {
            .sidebar a {
                text-align: center;
                float: none;
            }
        }

        #ch {
            position: fixed;
            text-align: center;
            overflow: hidden;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="sidebar">
        <a href="http://localhost/waste-management-system-main/index.html" class="fa fa-home">
            <strong> Home - <img src="Capture.PNG" alt="LOGO" height='30' width='30' class='logo1'></strong>
        </a>
    </div>
    <br>

    <div class="container" id="ch">
        <b><mark> YOUR COMPLAINT HISTORY </mark></b>
    </div>
    <div class="container">
        <table class="table">
            <br><br>
            <tr>
                <th>Date</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Type of Waste</th>
                <th>Location</th>
                <th>Location Description</th>
                <th>Images</th>
                <th>Status</th>
                <th colspan="2">Operations</th>
            </tr>

            <?php
            include("connection.php");

            $sessionEmail = $_SESSION['email'] ?? '';
            $hostForImage = "/waste-management-system-main/phpGmailSMTP/upload/";
            $query = "SELECT * FROM garbageinfo WHERE email = '$sessionEmail'";
            $data = mysqli_query($db, $query);
            $total = mysqli_num_rows($data);

            if ($total != 0) {
                while ($result = mysqli_fetch_assoc($data)) {
                    $imageFile = htmlspecialchars($result['file']);
                    $imagePath = $hostForImage . $imageFile;

                    ?>
                    <tr class='shadow p-3 mb-5 bg-white rounded'>
                        <td><?php echo htmlspecialchars($result['date']); ?></td>
                        <td><?php echo htmlspecialchars($result['name']); ?></td>
                        <td><?php echo htmlspecialchars($result['mobile']); ?></td>
                        <td><?php echo htmlspecialchars($result['email']); ?></td>
                        <td><?php echo htmlspecialchars($result['wastetype']); ?></td>
                        <td><?php echo htmlspecialchars($result['location']); ?></td>
                        <td><?php echo htmlspecialchars($result['locationdescription']); ?></td>
                        <td>
                            <?php if (!empty($result['file'])) { ?>
                                <a href="<?php echo $imagePath; ?>" target="_blank">
                                    <img src="<?php echo $imagePath; ?>" height="200" width="200" 
                                         onerror="this.onerror=null; this.src='fallback.png';"/>
                                </a>
                            <?php } else {
                                echo "<p style='color:red;'>No image available</p>";
                            } ?>
                        </td>
                        <td><?php echo htmlspecialchars($result['status']); ?></td>
                        <td>
                            <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#exampleModalCenter"
                               onclick="modalLauch(<?php echo htmlspecialchars($result['Id']); ?>)">Delete</a>
                        </td>
                        <td>
                            <a href="update.php?i=<?php echo urlencode($result['Id']); ?>&n=<?php echo urlencode($result['name']); ?>&mbl=<?php echo urlencode($result['mobile']); ?>&em=<?php echo urlencode($result['email']); ?>&wt=<?php echo urlencode($result['wastetype']); ?>&lo=<?php echo urlencode($result['location']); ?>&lod=<?php echo urlencode($result['locationdescription']); ?>&f=<?php echo urlencode($result['file']); ?>&d=<?php echo urlencode($result['date']); ?>" 
                               class="btn btn-success">Edit</a>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
    </div>

    <div class='modal fade' id='exampleModalCenter' tabindex='-1' role='dialog' aria-labelledby='exampleModalCenterTitle' aria-hidden='true'>
        <div class='modal-dialog modal-dialog-centered' role='document'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title'>Delete</h5>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
                <div class='modal-body'>
                    Are you sure you want to delete this complaint?
                    <input id="toDeleteId" type="hidden" value="">
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-light' data-dismiss='modal'>Close</button>
                    <button type='button' class='btn btn-danger' onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var delId;
        function modalLauch(id) {
            delId = id;
            document.getElementById('toDeleteId').value = id;
        }
        function confirmDelete() {
            window.location.replace("http://localhost/waste-management-system-main/adminlogin/delete.php?i=" + delId);
        }
    </script>
</body>
</html>
