<?php 
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "jobportal_db";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $db :" . $e->getMessage());
}

function handleFileUpload($inputName) {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$inputName]['tmp_name'];
        $fileName = basename($_FILES[$inputName]['name']);
        $uploadDir = '../uploads/';
        $uploadFilePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
            return $uploadFilePath;
        } else {
            echo "Error moving uploaded file.";
            return null;
        }
    }
    return null;
}

$about = $_POST['about'] ?? '';
$skills = json_encode($_POST['skills'] ?? []);
$user_id = $_SESSION['user_id'];

$experienceIds = [];
$educationIds = [];

$user_id = $_SESSION['user_id'];

// Handle work experience
foreach ($_POST['experience'] as $index => $exp) {
    $startingTime = isset($exp['starting_time']) ? $exp['starting_time'] . '-01' : null;
    $leavingTime = isset($exp['leaving_time']) ? $exp['leaving_time'] . '-01' : null;

    if ($startingTime !== null && $leavingTime !== null) {
        $certificatePath = handleFileUpload("experience_certificate_$index");

        $job_title = trim($exp['job_title']);
        $company_name = trim($exp['company_name']);

        $sqlCheck = "SELECT id, image FROM experience WHERE user_id = :user_id ";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([
            ':user_id' => $user_id
        ]);
        $existingExp = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingExp) {

            // Use the old image if no new image was uploaded
            if (!$certificatePath) {
                $certificatePath = $existingExp['image'];
            }
            $sqlUpdate = "UPDATE experience SET job_title = :job_title, company_name = :company_name, starting_time = :starting_time, leaving_time = :leaving_time, image = :image, achievement = :achievement WHERE id = :id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':job_title' => $job_title,
                ':company_name' => $company_name,
                ':starting_time' => $startingTime,
                ':leaving_time' => $leavingTime,
                ':image' => $certificatePath,
                ':achievement' => $exp['achievement'],
                ':id' => $existingExp['id']
            ]);
            $experienceIds[] = $existingExp['id'];
            echo "Experience updated successfully.";
        } else {
            if ($certificatePath) {
                $sqlInsert = "INSERT INTO experience (user_id, job_title, company_name, starting_time, leaving_time, image, achievement) 
                              VALUES (:user_id, :job_title, :company_name, :starting_time, :leaving_time, :image, :achievement)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':user_id' => $user_id,
                    ':job_title' => $job_title,
                    ':company_name' => $company_name,
                    ':starting_time' => $startingTime,
                    ':leaving_time' => $leavingTime,
                    ':image' => $certificatePath,
                    ':achievement' => $exp['achievement']
                ]);
                $experienceIds[] = $pdo->lastInsertId();
                echo "Experience inserted successfully.";
            } else {
                echo "Failed to upload certificate for experience index $index.";
            }
        }
    } else {
        echo "Starting time or leaving time not set for index $index.";
    }
}


// Handle education update
if (isset($_POST['education'])) {
    foreach ($_POST['education'] as $index => $edu) {
        $startingTime = isset($edu['starting_time']) ? $edu['starting_time'] . '-01' : null;
        $leavingTime = isset($edu['leaving_time']) ? $edu['leaving_time'] . '-01' : null;

        if ($startingTime !== null && $leavingTime !== null) {
            $certificatePath = handleFileUpload("education_certificate_$index");

            $degree = trim($edu['degree']);
            $universityName = trim($edu['university_name']);

            // Check if education record exists
            $sqlCheckEdu = "SELECT id, image FROM education WHERE user_id = :user_id";
            $stmtCheckEdu = $pdo->prepare($sqlCheckEdu);
            $stmtCheckEdu->execute([':user_id' => $user_id]);
            $existingEdu = $stmtCheckEdu->fetch(PDO::FETCH_ASSOC);

            if ($existingEdu) {
                // Use the old image if no new image was uploaded
                if (!$certificatePath) {
                    $certificatePath = $existingEdu['image'];
                }

                // Update existing education record
                $sqlUpdateEdu = "UPDATE education SET degree = :degree, university_name = :university_name, starting_time = :starting_time, leaving_time = :leaving_time, image = :image, achievement = :achievement WHERE id = :id";
                $stmtUpdateEdu = $pdo->prepare($sqlUpdateEdu);
                $stmtUpdateEdu->execute([
                    ':degree' => $degree,
                    ':university_name' => $universityName,
                    ':starting_time' => $startingTime,
                    ':leaving_time' => $leavingTime,
                    ':image' => $certificatePath,
                    ':achievement' => $edu['achievement'],
                    ':id' => $existingEdu['id']
                ]);
                $educationIds[] = $existingEdu['id'];
                echo "Education updated successfully for index $index.";
            } else {
                // Insert new education record
                if ($certificatePath) {
                    $sqlInsertEdu = "INSERT INTO education (user_id, degree, university_name, starting_time, leaving_time, image, achievement) 
                    VALUES (:user_id, :degree, :university_name, :starting_time, :leaving_time, :image, :achievement)";
                    $stmtInsertEdu = $pdo->prepare($sqlInsertEdu);
                    $stmtInsertEdu->execute([
                        ':user_id' => $user_id,
                        ':degree' => $degree,
                        ':university_name' => $universityName,
                        ':starting_time' => $startingTime,
                        ':leaving_time' => $leavingTime,
                        ':image' => $certificatePath,
                        ':achievement' => $edu['achievement']
                    ]);
                    $educationIds[] = $pdo->lastInsertId();
                    echo "Education inserted successfully.";
                } else {
                    echo "Failed to upload certificate for education index $index.";
                }
            }
        } else {
            echo "Starting time or leaving time not set for index $index.";
        }
    }
}
// Check if CV already exists
$sqlCheckCV = "SELECT id FROM cv WHERE user_id = :user_id";
$stmtCheckCV = $pdo->prepare($sqlCheckCV);
$stmtCheckCV->execute([':user_id' => $user_id]);
$existingCV = $stmtCheckCV->fetch();

// Insert or update CV table
if ($existingCV) {
    $sqlUpdateCV = "UPDATE cv SET about = :about, skills = :skills, experienceid = :experienceid, educationid = :educationid WHERE id = :id";
    $stmtUpdateCV = $pdo->prepare($sqlUpdateCV);
    $stmtUpdateCV->execute([
        ':about' => $about,
        ':skills' => $skills,
        ':experienceid' => json_encode($experienceIds),
        ':educationid' => json_encode($educationIds),
        ':id' => $existingCV['id']
    ]);
    $_SESSION['resume_success'] = "CV updated successfully.";
} else {
    $sqlInsertCV = "INSERT INTO cv (user_id, about, skills, experienceid, educationid) 
                    VALUES (:user_id, :about, :skills, :experienceid, :educationid)";
    $stmtInsertCV = $pdo->prepare($sqlInsertCV);
    $stmtInsertCV->execute([
        ':user_id' => $user_id,
        ':about' => $about,
        ':skills' => $skills,
        ':experienceid' => json_encode($experienceIds),
        ':educationid' => json_encode($educationIds)
    ]);
    $_SESSION['resume_success'] = "CV created successfully.";
}

header("Location: resume.php");
exit;
?>