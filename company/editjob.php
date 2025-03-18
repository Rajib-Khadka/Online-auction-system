<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
    exit();
}

// Check if job ID is provided
if (!isset($_GET['id'])) {
    header("Location: managejob.php");
    exit();
}

$job_id = $_GET['id'];

// Query to fetch job details
$job_query = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($job_query);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job_result = $stmt->get_result();

$job = $job_result->fetch_assoc();

// Query to fetch job categories
$category_query = "SELECT * FROM categories";
$category_result = mysqli_query($conn, $category_query);

if ($category_result && mysqli_num_rows($category_result) > 0) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
} else {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/postjob.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    .update-job-form {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-row {
        display: flex;
        gap: 20px;
    }

    .form-column {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .dashboard-content {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .gender-selection {
        display: flex;
        flex-direction: row;
        gap: 10px;
        /* Adjust the gap as needed */
    }

    .gender-selection input[type="radio"] {
        margin-right: 10px;
        /* Space between radio button and label */
    }

    .gender-selection label {
        display: inline-block;
    }

    .action-container {
        display: flex;
        justify-content: flex-end;
    }

    .go-back-button {
        font-size: 1.2em;
        font-family: Georgia;
        font-weight: normal;
        padding: 8px 16px;
        background-color: rgb(77, 77, 236);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .go-back-button:hover {
        background-color: #0056b3;
    }

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
                    <h2 class="banner-heading">Edit Job</h2>
                    <p class="banner-paragraph">Business plan draws on a wide range of knowledge from different business
                        <br>
                        disciplines. Business draws on a wide range of different business.
                    </p>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a> | <a href="editjob.php?job_id=<?php echo $job_id; ?>">Edit
                            Job</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">
            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="location.href='postjob.php'"><i class="fas fa-plus"></i> Post a Job</div>
                <div class="nav-item" onclick="location.href='managejob.php'"><i class="fas fa-tasks"></i> Manage Job
                </div>
                <div class="nav-item" onclick="location.href='message.php'"><i class=" fas fa-comments"></i>
                    Messages
                </div>
                <div class="nav-item" onclick="location.href='setting.php'"><i class="fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <div class="dashboard-content">
                <?php
                    
                     // Display success or error messages if available
                     if (isset($_SESSION['job_post_success'])) {
                        echo '<div class="message" style="padding: 10px;">';
                        echo $_SESSION['job_post_success'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['job_post_success']);
                    }

                    if (isset($_SESSION['job_post_error'])) {
                        echo '<div class="error-message" style="padding: 10px;">';
                        echo $_SESSION['job_post_error'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['job_post_error']);
                    }
                    ?>
                <div class="action-container">
                    <button onclick="window.history.back()" class="go-back-button">Go Back</button>
                </div>
                <form action="process.php" method="POST" class="update-job-form">
                    <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                    <div class="form-row">
                        <div class="form-column">
                            <label for="job_title">Job Title</label>
                            <input type="text" id="job_title" name="job_title"
                                value="<?php echo htmlspecialchars($job['title']); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city"
                                value="<?php echo htmlspecialchars($job['city']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="job_type">Job Type</label>
                            <select id="job_type" name="job_type">
                                <option value="" disabled>Select Job Type</option>
                                <option value="Full Time"
                                    <?php echo ($job['type'] == 'Full Time') ? 'selected' : ''; ?>>
                                    Full Time
                                </option>
                                <option value="Part Time"
                                    <?php echo ($job['type'] == 'Part Time') ? 'selected' : ''; ?>>
                                    Part Time
                                </option>
                                <option value="Freelance"
                                    <?php echo ($job['type'] == 'Freelance') ? 'selected' : ''; ?>>
                                    Freelance
                                </option>
                            </select>
                        </div>
                        <div class="form-column">
                            <label for="job_category">Job Category</label>
                            <select id="job_category" name="job_category">
                                <option value="" disabled>Select Job Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                    <?php echo ($job['category'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="gender">Gender</label>
                            <div class="gender-selection">
                                <div>
                                    <input type="radio" id="male" name="gender" value="male"
                                        <?php echo ($job['gender'] == 'male') ? 'checked' : ''; ?>>
                                    <label for="male">Male</label>
                                </div>
                                <div>
                                    <input type="radio" id="female" name="gender" value="female"
                                        <?php echo ($job['gender'] == 'female') ? 'checked' : ''; ?>>
                                    <label for="female">Female</label>
                                </div>
                                <div>
                                    <input type="radio" id="any" name="gender" value="any"
                                        <?php echo ($job['gender'] == 'any') ? 'checked' : ''; ?>>
                                    <label for="any">Any</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-column">
                            <label for="job_vacancy">Job Vacancy</label>
                            <input type="number" id="job_vacancy" name="job_vacancy"
                                value="<?php echo htmlspecialchars($job['job_vacancy']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="salary_from">Salary From</label>
                            <input type="number" id="salary_from" name="salary_from"
                                value="<?php echo htmlspecialchars($job['salary_from']); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="salary_to">Salary To</label>
                            <input type="number" id="salary_to" name="salary_to"
                                value="<?php echo htmlspecialchars($job['salary_to']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="shift">Shift</label>
                            <select id="shift" name="shift">
                                <option value="" disabled>Select Shift</option>
                                <option value="Morning shift"
                                    <?php echo ($job['shift'] == 'Morning shift') ? 'selected' : ''; ?>>
                                    Morning shift
                                </option>
                                <option value="Evening shift"
                                    <?php echo ($job['shift'] == 'Evening shift') ? 'selected' : ''; ?>>
                                    Evening shift
                                </option>
                                <option value="Night shift"
                                    <?php echo ($job['shift'] == 'Night shift') ? 'selected' : ''; ?>>
                                    Night shift
                                </option>
                            </select>
                        </div>
                        <div class="form-column">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date"
                                value="<?php echo htmlspecialchars($job['expiry_date']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="career_level">Career Level</label>
                            <select id="career_level" name="career_level">
                                <option value="" disabled>Select Career Level</option>
                                <option value="Entry Level"
                                    <?php echo ($job['career_level'] == 'Entry Level') ? 'selected' : ''; ?>>
                                    Entry Level
                                </option>
                                <option value="Mid Level"
                                    <?php echo ($job['career_level'] == 'Mid Level') ? 'selected' : ''; ?>>
                                    Mid Level
                                </option>
                                <option value="Senior Level"
                                    <?php echo ($job['career_level'] == 'Senior Level') ? 'selected' : ''; ?>>
                                    Senior Level
                                </option>
                            </select>
                        </div>
                        <div class="form-column">
                            <label for="salary_period">Salary Period</label>
                            <select id="salary_period" name="salary_period">
                                <option value="" disabled>Select Salary Period</option>
                                <option value="Monthly"
                                    <?php echo ($job['salary_period'] == 'Monthly') ? 'selected' : ''; ?>>
                                    Monthly
                                </option>
                                <option value="Yearly"
                                    <?php echo ($job['salary_period'] == 'Yearly') ? 'selected' : ''; ?>>
                                    Yearly
                                </option>

                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="functional_area">Functional Area</label>
                            <input type="text" id="functional_area" name="functional_area"
                                value="<?php echo htmlspecialchars($job['functional_area']); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="degree_level">Degree Level</label>
                            <select id="degree_level" name="degree_level">
                                <option value="" disabled>Select Degree Level</option>
                                <option value="Bachelor's Degree"
                                    <?php echo ($job['degree_level'] == 'Bachelor') ? 'selected' : ''; ?>>
                                    Bachelor
                                </option>
                                <option value="Master's Degree"
                                    <?php echo ($job['degree_level'] == 'Master') ? 'selected' : ''; ?>>
                                    Master
                                </option>
                                <option value="PHD" <?php echo ($job['degree_level'] == 'PHD') ? 'selected' : ''; ?>>
                                    PHD
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">

                        <div class="form-column">
                            <label for="minexperience">Minimum Experience</label>
                            <input type="number" id="minexperience" name="minexperience"
                                value="<?php echo htmlspecialchars($job['min_experience']); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="maxexperience">Maximum Experience</label>
                            <input type="number" id="maxexperience" name="maxexperience"
                                value="<?php echo htmlspecialchars($job['max_experience']); ?>" required>
                        </div>

                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="skills">Skills</label>
                            <textarea id="skills" name="skills" rows="4"
                                required><?php echo htmlspecialchars($job['skills']); ?></textarea>
                        </div>

                    </div>
                    <label for="job_description">Job Description</label>
                    <div class="form-row">
                        <textarea id="job_description" name="job_description" rows="7"
                            required><?php echo htmlspecialchars($job['job_desc']); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="recruiter_name">Recruiter Name</label>
                            <input type="text" id="recruiter_name" name="recruiter_name"
                                value="<?php echo htmlspecialchars($job['Rname']); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="recruiter_email">Recruiter Email</label>
                            <input type="email" id="recruiter_email" name="recruiter_email"
                                value="<?php echo htmlspecialchars($job['Remail']); ?>" required>
                        </div>

                    </div>
                    <button type="submit" name="jobpost_update">Update Information</button>
                </form>

            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php'; ?>
</body>

</html>