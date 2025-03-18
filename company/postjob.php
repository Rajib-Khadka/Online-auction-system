<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
    exit();
}

require('../Config/dbconnect.php'); // Adjust the path as per your file structure


// Query to fetch job categories
$query = "SELECT * FROM categories";
$result = mysqli_query($conn, $query);

// Check if categories were fetched successfully
if ($result && mysqli_num_rows($result) > 0) {
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $categories = []; // Empty array if no categories found or error
}

// Check if job posted successfully
$job_posted_message = '';
if (isset($_GET['job_posted']) && $_GET['job_posted'] == 'success') {
    $job_posted_message = 'Job posted successfully.';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/postjob.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    .message,
    .error-message {
        color: white;
        font-size: 1.3em;
        font-weight: 400;
        margin-bottom: 20px;
        position: relative;
        padding: 0px;
        border-radius: 5px;
        background-color: #3498db;
        text-align: center;
    }

    .error-message {
        color: red;
    }

    .message .close-button,
    .error-message .close-button {
        position: absolute;
        top: 5px;
        right: 10px;
        cursor: pointer;
        font-size: 1.3em;
        color: white;
        background: transparent;
        border: none;
    }
    </style>
    <script>
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        // Show the 'Post a Job' form by default
        showForm('post-job');
    });

    function closeMessage() {
        document.querySelector('.success-message').style.display = 'none';
    }

    function showForm(formId) {
        console.log('Showing form:', formId); // Debugging line
        document.querySelectorAll('.content-section').forEach(section => {
            if (section.id === formId) {
                section.style.display = 'block'; // Show the selected section
            } else {
                section.style.display = 'none'; // Hide all other sections
            }
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        // Attach event listener for form submission
        document.querySelector('.post-job-form').addEventListener('submit', validateForm);
    });

    function validateForm(event) {
        let isValid = true;

        // Fetch input values
        const jobTitle = document.getElementById('job_title').value.trim();
        const jobCategory = document.getElementById('job_category').value;
        const jobVacancy = document.getElementById('job_vacancy').value;
        const salaryFrom = parseFloat(document.getElementById('salary_from').value);
        const salaryTo = parseFloat(document.getElementById('salary_to').value);
        const minExperience = parseFloat(document.getElementById('minexperience').value);
        const maxExperience = parseFloat(document.getElementById('maxexperience').value);
        const recruiterEmail = document.getElementById('recruiter_email').value;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Regex for email validation

        // Clear any previous error messages
        document.querySelectorAll('.error').forEach(error => {
            error.remove();
        });

        // Validate Job Title (required)
        if (!jobTitle) {
            showError('job_title', 'Job title is required.');
            isValid = false;
        }

        // Validate Job Category (required)
        if (!jobCategory) {
            showError('job_category', 'Job category is required.');
            isValid = false;
        }

        // Validate Job Vacancy (required and positive number)
        if (!jobVacancy || jobVacancy <= 0) {
            showError('job_vacancy', 'Please enter a valid number of job vacancies.');
            isValid = false;
        }

        // Validate Salary (from should be less than to)
        if (salaryFrom >= salaryTo) {
            showError('salary_from', 'Salary "From" must be less than "To".');
            isValid = false;
        }

        // Validate Experience (minimum should be less than maximum)
        if (minExperience >= maxExperience) {
            showError('minexperience', 'Minimum experience must be less than maximum experience.');
            isValid = false;
        }

        // Validate Recruiter Email (valid email format)
        if (!emailPattern.test(recruiterEmail)) {
            showError('recruiter_email', 'Please enter a valid email address.');
            isValid = false;
        }

        // If form is invalid, prevent submission
        if (!isValid) {
            event.preventDefault();
        }
    }

    // Function to display error message below the corresponding input field
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorElement = document.createElement('div');
        errorElement.className = 'error';
        errorElement.style.color = 'red';
        errorElement.style.marginTop = '5px';
        errorElement.textContent = message;
        field.insertAdjacentElement('afterend', errorElement); // Insert error after the input field
    }
    </script>

</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="#"><img src="../logo/logo3.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="postjob.php" class="search-button"
                        style="font-size: 1em; color: white; font-family: Georgia; font-weight: normal; padding: 6px 12px;">Post
                        a
                        Job</a>
                </li>
            </ul>
        </div>
    </nav>

    <section class="banner-section">
        <div class="banner-overlay">
            <div class="banner-container">
                <div class="banner-content">
                    <h2 class="banner-heading">Post Job</h2>
                    <p class="banner-paragraph">Business plan draws on a wide range of knowledge from different business
                        <br>
                        disciplines. Business draws on a wide range of different business.
                    </p>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a> | <a href="postjob.php">Post Job</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">

            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="showForm('post-job')"><i class="fas fa-plus"></i> Post a Job</div>
                <div class="nav-item" onclick="location.href='managejob.php'"><i class="fas fa-tasks"></i> Manage Job
                </div>
                <div class="nav-item" onclick="location.href='message.php'"><i class=" fas fa-comments"></i>
                    Messages
                </div>
                <div class="nav-item" onclick="location.href='setting.php'"><i class=" fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <div class="dashboard-content">
                <?php 
                    if (isset($_SESSION['job_post_success'])) {
                         echo '<div class="success-message" style="padding: 10px;">';
                        echo $_SESSION['job_post_success'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px; line-height: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        // Unset the session variable after displaying
                         unset($_SESSION['job_post_success']);
                        }
                        
                        if (isset($_SESSION['job_post_error'])) {
                            echo '<div class="error-message" style="padding: 10px;">';
                            echo $_SESSION['job_post_error'];
                            echo '<button class="close-button" style="margin: 0px; padding: 10px; line-height: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                            echo '</div>';
                            unset($_SESSION['job_post_error']);
                        }
                ?>
                <div id="post-job" class="content-section">
                    <p>Job Information</p>
                    <form action="process.php" method="POST" class="post-job-form">
                        <div class="form-row">
                            <div class="form-column">
                                <input type="hidden" name="company_name"
                                    value="<?php echo htmlspecialchars($_SESSION['company_name']); ?>">
                                <!-- Your form fields go here -->
                                <label for="job_title">Job Title</label>
                                <input type="text" id="job_title" name="job_title" required>

                                <label for="job_type">Job Type</label>
                                <select id="job_type" name="job_type">
                                    <option value="" disabled selected>Select Job Type</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="part Time">Part Time</option>
                                    <option value="freelance">Freelance</option>
                                </select>

                                <label>Gender</label>
                                <div class="gender-options">
                                    <div class="gender-label">
                                        <input type="radio" id="gender_male" name="gender" value="male">
                                        <label for="gender_male">Male</label>
                                    </div>
                                    <div class="gender-label">
                                        <input type="radio" id="gender_female" name="gender" value="female">
                                        <label for="gender_female">Female</label>
                                    </div>
                                    <div class="gender-label">
                                        <input type="radio" id="gender_any" name="gender" value="any">
                                        <label for="gender_any">Any</label>
                                    </div>
                                </div>

                                <label for="salary_from">Salary From</label>
                                <input type="number" id="salary_from" name="salary_from" required>

                                <label for="shift">Shift</label>
                                <select id="shift" name="shift">
                                    <option value="" disabled selected>Select Shift</option>
                                    <option value="Morning Shift">Morning Shift</option>
                                    <option value="Afternoon Shift">Afternoon Shift</option>
                                    <option value="Night Shift">Night Shift</option>
                                </select>

                                <label for="career_level">Career Level</label>
                                <select id="career_level" name="career_level">
                                    <option value="" disabled selected>Select Career Level</option>
                                    <option value="entry">Entry Level</option>
                                    <option value="mid">Mid Level</option>
                                    <option value="senior">Senior Level</option>
                                </select>

                                <label for="functional_area">Functional Area</label>
                                <input type="text" name="functional_area" id="functional_area" required>

                                <label for="minexperience">Minimum Experience</label>
                                <input type="number" id="minexperience" name="minexperience" required>


                            </div>


                            <div class="form-column">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required>

                                <label for="job_category">Job Category</label>
                                <select id="job_category" name="job_category" required>
                                    <option value="" disabled selected>Select Job Category</option>
                                    <?php foreach ($categories as $category) : ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo $category['category']; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="job_vacancy">Job Vacancy</label>
                                <input type="number" id="job_vacancy" name="job_vacancy" required>


                                <label for="salary_to">Salary To</label>
                                <input type="number" id="salary_to" name="salary_to" required>

                                <label for="expiry_date">Expiry Date</label>
                                <input type="date" id="expiry_date" name="expiry_date" required>

                                <label for="salary_period">Salary Period</label>
                                <select id="salary_period" name="salary_period">
                                    <option value="" disabled selected>Select Salary Period</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                                <label for="degree_level">Degree Level</label>
                                <select id="degree_level" name="degree_level">
                                    <option value="" disabled selected>Select Degree Level</option>
                                    <option value="bachelor">Bachelor's</option>
                                    <option value="master">Master's</option>
                                    <option value="phd">PHD</option>
                                </select>

                                <label for="maxexperience">Maximum Experience</label>
                                <input type="number" id="maxexperience" name="maxexperience" required>


                            </div>
                        </div>
                        <label for="skills">Skills</label>
                        <div class="form-row">
                            <textarea id="skills" name="skills" rows="6"></textarea>
                        </div>
                        <label for="job_description">Job Description</label>
                        <div class="form-row">
                            <textarea id="job_description" name="job_description" rows="6"></textarea>
                        </div>

                        <h4>Recruiter Information</h4>
                        <div class="form-row">
                            <div class="form-column">
                                <label for="recruiter_name">Full Name</label>
                                <input type="text" id="recruiter_name" name="recruiter_name" required>
                            </div>
                            <div class="form-column">
                                <label for="recruiter_email">Email</label>
                                <input type="email" id="recruiter_email" name="recruiter_email" required>
                            </div>
                        </div>

                        <button type="submit" name="jobpost">Post Job</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>