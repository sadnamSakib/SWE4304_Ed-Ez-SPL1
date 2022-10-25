<?php
$root_path = '../../../';
$profile_path = '../';
include $root_path . 'LibraryFiles/DatabaseConnection/config.php';
include $root_path . 'LibraryFiles/URLFinder/URLPath.php';
include $root_path . 'LibraryFiles/SessionStore/session.php';

session::create_or_resume_session();
session::profile_not_set($root_path);
$temp = hash('sha512', $_SESSION['email']);
$tableName = $_SESSION['tableName'];
$row = mysqli_fetch_assoc($database->performQuery("SELECT * FROM users WHERE email='$temp';"));
$className = 'Math 4341 Linear Algebra';
$classrooms = $database->performQuery("SELECT * FROM classroom,teacher_classroom where classroom.class_code=teacher_classroom.class_code and teacher_classroom.email='$temp';");
foreach ($classrooms as $dummy_classroom) {
  if (isset($_REQUEST['delete' . $dummy_classroom['class_code']])) {
    $database->performQuery("DELETE FROM teacher_classroom WHERE class_code='" . $dummy_classroom['class_code'] . "';");
    $database->performQuery("UPDATE classroom  SET active='0' WHERE class_code='" . $dummy_classroom['class_code'] . "';");
  }
}

if (isset($_POST['Join'])) {
  $classCode = $_REQUEST['classCode'];
  $existenceCheck = $database->performQuery("SELECT * FROM teacher_classroom WHERE class_code='$classCode' and email='$temp';");
  if ($database->performQuery("SELECT * FROM classroom WHERE class_code='$classCode'")->num_rows == 0) {
    $error = "classroom doesn't exist";
  } else if ($existenceCheck->num_rows === 0) {
    $database->performQuery("INSERT INTO teacher_classroom(email,class_code) VALUES('$temp','$classCode');");
  } else {
    $error = "You have already joined in this classroom";
  }
  unset($_REQUEST['classCode']);
}


if (isset($_POST['Create'])) {
  $classCode = generateRandomString(10);
  $existence = $database->performQuery("SELECT * FROM classroom where class_code='$classCode'");
  while ($existence->num_rows > 0) {
    $classCode = generateRandomString(10);
    $existence = $database->performQuery("SELECT * FROM classroom where class_code='$classCode'");
  }
  $className = $_POST['courseName'];
  $courseCode = $_POST['courseCode'];
  $semester = $_POST['semester'];
  $database->performQuery("INSERT INTO classroom(class_code,classroom_name,course_code,semester) VALUES('$classCode','$className','$courseCode','$semester');");
  $database->performQuery("INSERT INTO teacher_classroom(email,class_code) VALUES('$temp','$classCode');");
}

$classrooms = $database->performQuery("SELECT * FROM classroom,teacher_classroom where classroom.class_code=teacher_classroom.class_code and teacher_classroom.email='$temp' and classroom.active='1';");
foreach ($classrooms as $dummy_classroom) {
  if (isset($_POST[$dummy_classroom['class_code']])) {
    $_SESSION['class_code'] = $dummy_classroom['class_code'];
    header('Location: TeacherClassroom/index.php');
  }
}

foreach ($classrooms as $dummy_classroom) {
  if (isset($_POST["view" . $dummy_classroom['class_code']])) {
    $_SESSION['class_code'] = $dummy_classroom['class_code'];
    header('Location: ViewDetails/index.php');
  }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Classroom</title>
  <link rel="icon" href="<?php echo $root_path; ?>title_icon.jpg" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="<?php echo $root_path; ?>css/bootstrap.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <link href="<?php echo $root_path;?>boxicons-2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script defer src="script.js"></script>
  <?php include 'ClassroomSystemScript.php';?>
  <?php include 'ClassroomSystemStyle.php';?>
</head>

<body>
  <script src="<?php echo $root_path; ?>js/bootstrap.js"></script>
  <div class="main-container d-flex">
    <?php
    include $profile_path . 'navbar.php';
    teacher_navbar($root_path);
    ?>
    <section class="content-section m-auto px-1 w-75">
      <div class="container justify-content-evenly">
        <div class="container bg-white rounded mt-5 mb-5"></div>
        <div class="px-3 me-3 d-flex flex-row-reverse">
          <button type="button" class="btn btn-outline-primary btn-join d-flex p-4 py-3" data-bs-toggle="modal" data-bs-target="#examplemodal1" data-bs-whatever="@fat"><b>Join new classroom</b></button>
          <div class="modal fade" id="examplemodal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Join classroom</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="" method="POST">
                    <div class="mb-3">
                      <input type="text" name="classCode" class="form-control" placeholder="Enter classroom code" aria-label="Leave a comment">
                    </div>

                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <input type="submit" name="Join" value="Join" class="btn btn-primary btn-join">
                </div>
                </form>
              </div>
            </div>
          </div>

          <div class="px-3 me-3 d-flex flex-row-reverse">
            <button type="button" class="btn btn-outline-primary btn-join d-flex p-4 py-3" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@fat"><b>Create new classroom</b></button>
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Create classroom</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action='' id='addCourse' method='POST'>
                      <div class="mb-3" id="error" style="display:none">
                      </div>
                      <div class="mb-3">
                        <input type="text" id="courseName" name="courseName" class="form-control" placeholder="Enter Course Name" aria-label="Leave a comment">
                      </div>
                      <div class="mb-3">
                        <input type="text" id="courseCode" name="courseCode" class="form-control" placeholder="Enter Course Code" aria-label="Leave a comment">
                      </div>
                      <div class="mb-3">
                        <input type="text" id="semester" name="semester" class="form-control" placeholder="Enter Semester" aria-label="Leave a comment">
                      </div>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <input type='submit' name='Create' value='Create' class="btn btn-primary btn-join">
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container bg-white rounded m-auto justify-content-center mt-5 mb-5"></div>
      <div class="row justify-content-start m-auto">
        <div id="error" style="color:red">
          <?php echo $error; ?>
        </div>
        <!-- <h2 class="fs-5">Profile</h2> -->
        <?php
        foreach ($classrooms as $i) {
        ?>
          <div class="card-element col-lg-4 col-md-6 p-4 px-2">
            <div class="card card-box-shadow">
              <div class="card-header  task-card justify-content-around" style="height:100px">
                <div class="row">
                  <h4 class="card-title col py-2"><?php echo $i['course_code'] . ": " . $i['classroom_name']; ?></h4>
                  <?php $card = $i['class_code']; ?>
                  <div class="dropdown col-lg-auto col-sm-6 col-md-3 py-3">
                    <i onclick="<?php echo $card; ?>dropdownbtn()" class="<?php echo $card; ?>dropbtn bx bx-dots-horizontal-rounded"></i>
                    <div id="<?php echo $card; ?>myDropdown" class="<?php echo $card; ?>dropdown-content dropdown-menu">
                      <form name='view_delete<?php echo $card; ?>' action='' method='POST'>
                        <input type="submit" value="View Details" name='view<?php echo $card; ?>' class="dropdown-item">
                        <input type="submit" value="Delete Classroom" name='delete<?php echo $card; ?>' class="dropdown-item">
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <p class="card-text"><?php
                                      $class_code = $i['class_code'];
                                      $sql = $database->performQuery("SELECT * FROM teacher_classroom,users WHERE teacher_classroom.email=users.email AND class_code='$class_code'");
                                      ?></p>
                <?php
                foreach ($sql as $j) {
                ?>
                  <p class="card-text"><?php echo "Course Instructor(s): " . $j['name']; ?></p>
                <?php
                }
                ?>
                <p class="card-text"><?php echo "Class Code: " . $i['class_code']; ?></p>
                <div class="pb-3 px-5">
                  <form id="EnterClassroom" name="EnterClassroom" action="" method="POST">
                    <input type="submit" name="<?php echo  $i['class_code']; ?>" value="Enter Class" class="btn btn-primary btn-go">
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php
        }
        ?>
      </div>
    </section>
  </div>
  </div>

</body>

</html>