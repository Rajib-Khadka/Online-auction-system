<?php
session_start();
?>
<?php
include '../Config/dbconnect.php';

// Handle user information update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
// Retrieve and sanitize POST data
$user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
$user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
$gender = mysqli_real_escape_string($conn, $_POST['gender']);
$maritalstatus = mysqli_real_escape_string($conn, $_POST['maritalstatus']);
$immediateavailable = mysqli_real_escape_string($conn, $_POST['immediateavailable']);
$user_dob = mysqli_real_escape_string($conn, $_POST['user_dob']);
$skills = mysqli_real_escape_string($conn, $_POST['skills']);
$language = mysqli_real_escape_string($conn, $_POST['language']);
$nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
$NID = mysqli_real_escape_string($conn, $_POST['NID']);
$experience = mysqli_real_escape_string($conn, $_POST['experience']);
$career_level = mysqli_real_escape_string($conn, $_POST['career_level']);
$industry = mysqli_real_escape_string($conn, $_POST['industry']);
$functional_area = mysqli_real_escape_string($conn, $_POST['functional_area']);
$currentsalary = mysqli_real_escape_string($conn, $_POST['currentsalary']);
$expectsalary = mysqli_real_escape_string($conn, $_POST['expectsalary']);
$country = mysqli_real_escape_string($conn, $_POST['country']);
$state = mysqli_real_escape_string($conn, $_POST['state']);
$city = mysqli_real_escape_string($conn, $_POST['city']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$fb_url = mysqli_real_escape_string($conn, $_POST['fb_url']);
$linkedin_url = mysqli_real_escape_string($conn, $_POST['linkedin_url']);

$sql = "UPDATE user SET
        email = '$user_email',
        gender = '$gender',
        marital_status = '$maritalstatus',
        immediate_available = '$immediateavailable',
        date_of_birth = '$user_dob',
        skill = '$skills',
        language = '$language',
        nationality = '$nationality',
        national_id_card = '$NID',
        experience = '$experience',
        career_level = '$career_level',
        industry = '$industry',
        functional_area = '$functional_area',
        current_salary = '$currentsalary',
        expected_salary = '$expectsalary',
        country = '$country',
        state = '$state',
        city = '$city',
        address = '$location',
        facebook_url = '$fb_url',
        linkedin_url = '$linkedin_url'
        WHERE name = '$user_name'";

if ($conn->query($sql) === TRUE) {
    // Assuming user_id is stored in session
    $user_id = $_SESSION['user_id'];

    // Clear previous skills from user_skills table for this user
    $sql_clear_skills = "DELETE FROM user_skills WHERE userid = '$user_id'";
    $conn->query($sql_clear_skills);  // No issue if there are no previous skills

    // Split the skills string into an array
    $skills_array = explode(',', $skills);  // Assuming skills are comma-separated

    // Insert each skill into the user_skills table
    foreach ($skills_array as $skill) {
        $skill = trim($skill);  // Clean up any extra whitespace
        if (!empty($skill)) {  // Ensure the skill is not an empty string
            $sql_insert_skill = "INSERT INTO user_skills (userid, skill_name) VALUES ('$user_id', '$skill')";
            $conn->query($sql_insert_skill);  // Insert skill
        }
    }
$_SESSION['user_update_success'] = "Your information updated successfully.";
} else {
$_SESSION['user_update_error'] = "Error updating record: " . $conn->error;
}

$conn->close();
header("Location: update_profile.php");
exit();
}

//user password changed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
        // Retrieve and sanitize POST data
        $old_password = $_POST['old_password'];
        $old_password_hash = md5($old_password); // Hash the old password with MD5
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
    
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT password FROM user WHERE userid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $current_password_hash = $row['password'];
    
        // Check if old password is correct
        if ($old_password_hash === $current_password_hash) {
            if ($new_password === $confirm_new_password) {
                // Hash the new password with MD5 (if you still want to use MD5, but bcrypt is recommended)
                $new_password_hash = md5($new_password);
    
                // Update the password
                $sql = "UPDATE user SET password = ? WHERE userid = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $new_password_hash, $user_id);
                $stmt->execute();
    
                if ($stmt->affected_rows > 0) {
                    $_SESSION['user_pass_changed'] = "Password updated successfully.";
                } else {
                    $_SESSION['user_pass_notchanged'] = "Error updating password.";
                }
            } else {
                $_SESSION['user_pass_notchanged'] = "New passwords do not match.";
            }
        } else {
            $_SESSION['user_pass_notchanged'] = "Old password is incorrect.";
        }
    
        $stmt->close();
        $conn->close();
        header("Location: change_pass.php");
        exit();
    }

?>