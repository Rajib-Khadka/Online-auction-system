<?php
session_start();
?>
<?php
include '../Config/dbconnect.php';

// for the job post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['jobpost'])) {
    // Escape user inputs for security
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $job_type = mysqli_real_escape_string($conn, $_POST['job_type']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $salary_from = mysqli_real_escape_string($conn, $_POST['salary_from']);
    $career_level = mysqli_real_escape_string($conn, $_POST['career_level']);
    $functional_area = mysqli_real_escape_string($conn, $_POST['functional_area']);
    $skills = mysqli_real_escape_string($conn, $_POST['skills']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $job_vacancy = mysqli_real_escape_string($conn, $_POST['job_vacancy']);
    $job_category = mysqli_real_escape_string($conn, $_POST['job_category']);
    $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    $salary_to = mysqli_real_escape_string($conn, $_POST['salary_to']);
    $salary_period = mysqli_real_escape_string($conn, $_POST['salary_period']);
    $shift = mysqli_real_escape_string($conn, $_POST['shift']);
    $degree_level = mysqli_real_escape_string($conn, $_POST['degree_level']);
    $minexperience = mysqli_real_escape_string($conn, $_POST['minexperience']);
    $maxexperience = mysqli_real_escape_string($conn, $_POST['maxexperience']);
    $job_description = mysqli_real_escape_string($conn, $_POST['job_description']);
    $recruiter_name = mysqli_real_escape_string($conn, $_POST['recruiter_name']);
    $recruiter_email = mysqli_real_escape_string($conn, $_POST['recruiter_email']);
    $create_datetime = date("Y-m-d");

    // Insert query
    $sql = "INSERT INTO jobs (title, city, company_name, type, category, gender, job_vacancy, salary_from, 
            salary_to, shift, posted_date, expiry_date, career_level, salary_period, 
            degree_level, functional_area, skills, job_desc, Rname, Remail, min_experience, max_experience)
            VALUES ('$job_title', '$city', '$company_name', '$job_type', '$job_category', '$gender', '$job_vacancy', '$salary_from',
             '$salary_to', '$shift', '$create_datetime', '$expiry_date', '$career_level', '$salary_period', 
             '$degree_level', '$functional_area', '$skills', '$job_description', '$recruiter_name', '$recruiter_email', '$minexperience', '$maxexperience')";

if ($conn->query($sql) === TRUE) {
    // Get the last inserted job ID
    $job_id = $conn->insert_id;

    // Split skills and insert into job_skills table
    $skills_array = explode(',', $skills);  // Assuming skills are comma-separated
    foreach ($skills_array as $skill) {
        $skill = trim($skill);  // Clean up whitespace
        $sql_skill = "INSERT INTO job_skills (job_id, skill_name) VALUES ('$job_id', '$skill')";
        $conn->query($sql_skill);  // Insert each skill
    }
    // Set success message in session variable
    $_SESSION['job_post_success'] = "Job posted successfully.";
} else {
    $_SESSION['job_post_error'] = "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
header("Location: postjob.php");
exit();
}

// Handle company information update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    // Retrieve and sanitize POST data
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $company_email = mysqli_real_escape_string($conn, $_POST['company_email']);
    $fax_no = mysqli_real_escape_string($conn, $_POST['fax_no']);
    $company_website = mysqli_real_escape_string($conn, $_POST['company_website']);
    $company_ceo = mysqli_real_escape_string($conn, $_POST['company_ceo']);
    $established_in = mysqli_real_escape_string($conn, $_POST['established_in']);
    $industry = mysqli_real_escape_string($conn, $_POST['industry']);
    $no_of_office = mysqli_real_escape_string($conn, $_POST['no_of_office']);
    $company_details = mysqli_real_escape_string($conn, $_POST['company_details']);
    $aboutus = mysqli_real_escape_string($conn, $_POST['aboutus']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $fb_url = mysqli_real_escape_string($conn, $_POST['fb_url']);
    $linkedin_url = mysqli_real_escape_string($conn, $_POST['linkedin_url']);

    $sql = "UPDATE company SET 
                email = '$company_email', 
                fax_no = '$fax_no', 
                website = '$company_website', 
                company_ceo = '$company_ceo', 
                established_in = '$established_in', 
                industry = '$industry', 
                no_of_office = '$no_of_office', 
                company_details = '$company_details', 
                aboutus = '$aboutus', 
                country = '$country', 
                state = '$state', 
                city = '$city', 
                location = '$location', 
                fb_url = '$fb_url', 
                linkedin_url = '$linkedin_url' 
            WHERE name = '$company_name'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['company_update_success'] = "Company information updated successfully.";
    } else {
        $_SESSION['company_update_error'] = "Error updating record: " . $conn->error;
    }

    $conn->close();
    header("Location: update_profile.php");
    exit();
}


//company password changed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    // Retrieve and sanitize POST data
    $old_password = $_POST['old_password'];
    $old_password_hash = md5($old_password); // Hash the old password with MD5
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    $company_name = $_SESSION['company_name'];
    $sql = "SELECT password FROM company WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $company_name);
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
            $sql = "UPDATE company SET password = ? WHERE name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_password_hash, $company_name);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['company_pass_changed'] = "Password updated successfully.";
            } else {
                $_SESSION['company_pass_notchanged'] = "Error updating password.";
            }
        } else {
            $_SESSION['company_pass_notchanged'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['company_pass_notchanged'] = "Old password is incorrect.";
    }

    $stmt->close();
    $conn->close();
    header("Location: change_pass.php");
    exit();
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['jobpost_update'])) {
    // Retrieve and sanitize the form data
    $job_id = intval($_POST['job_id']); // Retrieve the job ID from the hidden field
    $job_title = htmlspecialchars($_POST['job_title']);
    $city = htmlspecialchars($_POST['city']);
    $job_type = htmlspecialchars($_POST['job_type']);
    $job_category = htmlspecialchars($_POST['job_category']);
    $gender = htmlspecialchars($_POST['gender']);
    $job_vacancy = intval($_POST['job_vacancy']);
    $salary_from = intval($_POST['salary_from']);
    $salary_to = intval($_POST['salary_to']);
    $shift = htmlspecialchars($_POST['shift']);
    $expiry_date = htmlspecialchars($_POST['expiry_date']);
    $career_level = htmlspecialchars($_POST['career_level']);
    $salary_period = htmlspecialchars($_POST['salary_period']);
    $degree_level = htmlspecialchars($_POST['degree_level']);
    $functional_area = htmlspecialchars($_POST['functional_area']);
    $minexperience = mysqli_real_escape_string($conn, $_POST['minexperience']);
    $maxexperience = mysqli_real_escape_string($conn, $_POST['maxexperience']);
    $skills = htmlspecialchars($_POST['skills']);
    $job_description = htmlspecialchars($_POST['job_description']);
    $recruiter_name = htmlspecialchars($_POST['recruiter_name']);
    $recruiter_email = htmlspecialchars($_POST['recruiter_email']);

    // Validate the data (basic validation shown here, adjust as needed)
    if (empty($job_title) || empty($city) || empty($job_type) || empty($job_category) || empty($job_vacancy) || empty($shift) || empty($expiry_date) || empty($functional_area) || empty($minexperience) || empty($maxexperience) || empty($skills) || empty($recruiter_name) || empty($recruiter_email)) {
        $_SESSION['company_update_error'] = "Please fill in all required fields.";
        header("Location: editjob.php");
        exit();
    }

    // Prepare an SQL query to update the job details
    $update_query = "UPDATE jobs SET 
            title = ?, 
            city = ?, 
            type = ?, 
            category = ?, 
            gender = ?, 
            job_vacancy = ?, 
            salary_from = ?, 
            salary_to = ?, 
            shift = ?, 
            expiry_date = ?, 
            career_level = ?, 
            salary_period = ?, 
            degree_level = ?, 
            functional_area = ?, 
            min_experience = ?, 
            max_experience = ?, 
            skills = ?, 
            job_desc = ?, 
            Rname = ?, 
            Remail = ?
        WHERE id = ?"; // Corrected

    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param(
            "sssssiiissssssiissssi",
            $job_title, $city, $job_type, $job_category, $gender, $job_vacancy, $salary_from, $salary_to,
            $shift, $expiry_date, $career_level, $salary_period, $degree_level, $functional_area, $minexperience, 
            $maxexperience, $skills, $job_description, $recruiter_name, $recruiter_email, $job_id // Note job_id is last
        );

        if ($stmt->execute()) {
             // Clear existing skills for the job
             $delete_skills_query = "DELETE FROM job_skills WHERE job_id = ?";
             if ($delete_stmt = $conn->prepare($delete_skills_query)) {
                 $delete_stmt->bind_param("i", $job_id);
                 $delete_stmt->execute();
                 $delete_stmt->close();
             }
 
             // Split skills and insert into job_skills table
             $skills_array = explode(',', $skills);  // Assuming skills are comma-separated
             foreach ($skills_array as $skill) {
                 $skill = trim($skill);  // Clean up whitespace
                 if (!empty($skill)) { // Ensure the skill is not empty
                     $sql_skill = "INSERT INTO job_skills (job_id, skill_name) VALUES (?, ?)";
                     if ($skill_stmt = $conn->prepare($sql_skill)) {
                         $skill_stmt->bind_param("is", $job_id, $skill);
                         $skill_stmt->execute();
                         $skill_stmt->close();
                     }
                 }
             }
            $_SESSION['job_post_success'] = "Job updated successfully.";
        } else {
            $_SESSION['job_post_error'] = "Error updating job. Please try again.";
        }

        $stmt->close();
    } else {
        $_SESSION['job_post_error'] = "Error preparing update query. Please try again.";
    }

    $conn->close();
    header("Location: editjob.php");
    exit();
}
?>