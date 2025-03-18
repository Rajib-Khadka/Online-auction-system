<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Fetch the last 7 categories
$sql = "SELECT category FROM categories ORDER BY id ASC LIMIT 7"; // Adjust the table name and column as necessary
$result = $conn->query($sql);

// Store categories in an array
$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Fetch job listings from the jobs table
$sql1 = "SELECT * FROM jobs ORDER BY CASE WHEN expiry_date >= CURDATE() THEN 0 ELSE 1 END, expiry_date ASC LIMIT 2";  // Adjust the table name if necessary
$result1 = $conn->query($sql1);

function getUserData($userid) {
    global $conn; // Assuming you have a database connection established
    $query = "SELECT * FROM user WHERE userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $userid); // Binding parameter
    $stmt->execute(); // Execute the statement
    return $stmt->get_result()->fetch_assoc(); // Fetching the result without any arguments
}

function getJobData() {
    global $conn; // Assuming you have a database connection established
    $query = "SELECT * FROM jobs";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set from the prepared statement

    // Fetch all rows as an associative array
    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row; // Add each row to the jobs array
    }

    return $jobs; // Return the array of jobs
}
function calculateJobScore($job, $user) {
    $score = 0;

    // Skills Match (40%)
    $userSkills = isset($user['skill']) ? explode(',', $user['skill']) : [];
    $jobSkills = isset($job['skills']) ? explode(',', $job['skills']) : [];
    $skillMatchCount = count(array_intersect($userSkills, $jobSkills));
    $skillsWeight = !empty($jobSkills) ? ($skillMatchCount / count($jobSkills)) * 0.4 * 100 : 0;
    $score += $skillsWeight;

    // Job Preferences (25%)
    if (isset($user['career_level']) && $user['career_level'] == $job['career_level']) {
        $score += 0.25 * 100; // Full score for career level match
    }
    if (isset($user['city']) && $user['city'] == $job['city']) {
        $score += 0.25 * 100; // Full score for city match
    }
    if (isset($user['expected_salary']) && $user['expected_salary'] >= $job['salary_from'] && $user['expected_salary'] <= $job['salary_to']) {
        $score += 0.25 * 100; // Full score for salary range match
    }

    // Experience (20%)
    $minExperience = 0;
    $maxExperience = PHP_INT_MAX; // Default if no experience provided

    // Use the correct fields from your job table
    if (isset($job['min_experience']) && isset($job['max_experience'])) {
        $minExperience = (int)$job['min_experience'];
        $maxExperience = (int)$job['max_experience'];
    }

    // Check user's experience against job's experience requirements
    if (isset($user['experience']) && $user['experience'] >= $minExperience && $user['experience'] <= $maxExperience) {
        $score += 0.2 * 100; // Full score for experience match
    }

    // Company Factors (15%)
    if (isset($user['industry']) && $user['industry'] == $job['functional_area']) {
        $score += 0.15 * 100; // Full score for industry match
    }

    return $score;
}


function getJobDataById($jobId) {
    global $conn; // Assuming you have a database connection established
    $query = "SELECT * FROM jobs WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $jobId); // Assuming jobId is an integer
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();

    // Fetch company details based on company name or ID
    if ($job) {
        $companyName = $job['company_name']; // Assuming this field exists in your jobs table
        $companyQuery = "SELECT logo, country FROM company WHERE name = ?";
        $companyStmt = $conn->prepare($companyQuery);
        $companyStmt->bind_param('s', $companyName); // Assuming company name is a string
        $companyStmt->execute();
        $company = $companyStmt->get_result()->fetch_assoc();

        // Attach the logo to the job details array
        if ($company) {
            $job['logo'] = $company['logo']; // Add the logo to the job details
            $job['country'] = $company['country'];
        } else {
            $job['logo'] = '../uploads/userphoto/default.jpg'; // Default logo if company not found
                        $job['country'] = 'Unknown'; // Default country if company not found

        }
    }

    return $job; // Return job data with company logo
}




?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/footer.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <script>
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('.nav-links a');
        links.forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    });
    </script>
</head>

<body>

    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="http://localhost/JobPortal/index.php"><img src="http://localhost/JobPortal/Logo/logo3.png"
                        alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="category.php">Category</a></li>
                <li><a href="job.php">Jobs</a></li>
                <li>
                    <?php $user_id = $_SESSION['user_id'];
                        $query = "SELECT name FROM user WHERE userid = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('s', $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        $user_name = htmlspecialchars($user['name']);
                    ?>
                    <a href="profile.php" class="username">
                        <i class="fa fa-lock" aria-hidden="true" style="padding-left: 0px; font-size: 14px;"></i>
                        <?php echo $user_name; ?>
                    </a>
                </li>


            </ul>
        </div>
    </nav>
    <section class="hero">
        <div class="hero-container">
            <div class="hero-left">
                <div class="hero-text">
                    <h1>Find your career <br> to make a better life</h1>
                </div>
                <form action="job.php">

                    <div class="search-box">
                        <div class="input-group">
                            <label for="what">What</label>
                            <input type="text" id="what" placeholder="What jobs you want?" class="search-input">
                        </div>
                        <div class="input-group">
                            <label for="where">Where</label>
                            <input type="text" id="where" placeholder="Location" class="search-input">
                        </div>
                        <button class="search-button">Search</button>
                    </div>
                </form>
            </div>

            <div class="hero-image">
                <img src="../Banners/banner1.png" alt="Career Image">
            </div>
        </div>
    </section>

    <section class="steps-container">
        <div class="step">
            <i class="fa fa-user-circle" aria-hidden="true"></i>
            <div class="step-text">
                <h2>Register Your Account</h2>
                <p>Start by creating your personal profile to access countless job opportunities.</p>
            </div>
        </div>
        <div class="step">
            <i class="fa fa-upload" aria-hidden="true"></i>
            <div class="step-text">
                <h2>Upload Your Resume</h2>
                <p>Showcase your skills and experience by uploading your resume.</p>
            </div>
        </div>
        <div class="step">
            <i class="fa fa-briefcase" aria-hidden="true"></i>
            <div class="step-text">
                <h2>Apply for Your Dream Job</h2>
                <p>Browse through job listings and apply directly for the positions
                    that suit your career goals.</p>
            </div>
        </div>
    </section>


    <section class="category-section">
        <a href="category.php" style="text-decoration: none;">
            <button class="category-button">JOB CATEGORY</button>
        </a>
        <h2 class="category-heading">Choose Your Desired Category</h2>
        <p class="category-paragraph">
            Find the perfect career path by selecting from a wide range of categories. Whether you're exploring new
            industries or deepening your expertise, we've got options tailored to your goals.
        </p>

        <div class="category-box-container">
            <?php
            foreach ($categories as $category) {

                $icon = 'fa-briefcase'; // Default icon
        
                // Set specific icons based on the category name
                switch ($category) {
                    case 'Accounting':
                        $icon = 'fa-calculator';
                        break;
                    case 'Auditing':
                        $icon = 'fa-chart-line';
                        break;
                    case 'Banking and financial Services':
                        $icon = 'fa-university';
                        break;
                    
                    case 'CEO and General Management':
                        $icon = 'fa-building';
                        break;
                    case 'Community and Social Department':
                        $icon = 'fa-users';
                        break;
                    case 'Creative and Design':
                        $icon = 'fa-paint-brush';
                        break;
                    case 'Education and Training':
                        $icon = 'fa-graduation-cap';
                        break;
                }
        
    // Loop through the categories and create boxes
        echo '<div class="category-box">';
        echo '<a href="category.php?category=' . urlencode($category) . '" style="text-decoration: none;">';

        echo '<i class="fa ' . $icon . '" aria-hidden="true"></i>'; // Use the selected icon
        echo '<p>' . htmlspecialchars($category) . '</p>'; // Escape output for safety
        echo '</div>';
    }
    ?>
            <div class="category-box">
                <i class="fa fa-plus" aria-hidden="true"></i> <!-- Example icon -->
                <p><a href="category.php">Others</a></p>
            </div>
        </div>
    </section>

    <section class="banner-section">
        <div class="banner-overlay">
            <button class="get-started-button">Get Started To Work</button>
            <h2 class="banner-heading">Don't just find. Be found. Put your cv in <br> fornt of great employers.</h2>
            <p class="banner-paragraph">It helps you to increase your chances of finding a suitable job and
                let recruiters contact you about jobs that are <br>not needed to pay for advertising.
            </p>
            <button class="upload-cv-button"><i class="fa fa-upload" aria-hidden="true"></i>Upload Your Resume</button>
        </div>
    </section>

    <section class="hot-jobs-section">
        <div class="container">
            <h2 class="recent-jobs-heading">Recent Recommended Jobs</h2>

            <p class="recent-jobs-paragraph">
                Explore our latest job recommendations tailored to your skills and preferences. Stay ahead in your
                career by discovering exciting opportunities just for you!
            </p>
            <div class="category-box-container1">
                <div class="job-listings1">
                    <?php
            $userId = $_SESSION['user_id'];
            $user = getUserData($userId); // Fetch the user data
            $allJobs = getJobData(); // Fetch all job data
            
            $jobScores = []; // Array to hold job scores
            
            foreach ($allJobs as $job) {
                $score = calculateJobScore($job, $user); // Calculate the score for each job
                if ($score > 0) { // Only consider jobs with a positive score
                    $jobScores[$job['id']] = $score; // Store the score with job ID
                }
            }
            
            // Sort jobs by score in descending order
            arsort($jobScores);
            
            // Display the top recommended jobs (limit to, e.g., 5 jobs)
            $recommendedCount = 0;
            // Assuming you have jobScores array ready
            foreach ($jobScores as $jobId => $score) {
    if ($recommendedCount >= 4) break; // Limit to 5 recommendations
    
    // Fetch job details by job ID
    $jobDetails = getJobDataById($jobId);
    if ($jobDetails) {
        echo '<div class="job1" onclick="window.location.href=\'job_details.php?id=' . $jobDetails['id'] . '\'">';
        echo '<div class="img-logo">';
        echo '<img src="../uploads/' . htmlspecialchars($jobDetails['logo']) . '" alt="Company Logo" class="company-logo">'; // Ensure you have the logo in job details
        echo '</div>';
        echo '<div class="job-details">';
        echo '<span class="job-title">' . htmlspecialchars($jobDetails['title']) . '</span>';
        echo '<button class="job-type">' . ucfirst($jobDetails['type']) . '</button>';
        echo '<hr class="divider">';
        echo '<p class="job-description">' . htmlspecialchars(substr($jobDetails['job_desc'], 0, 100)) . '...</p>';
        echo '<div class="job-info">';
        echo '<p class="info-box">' . htmlspecialchars(substr($jobDetails['company_name'], 0, 15)) . '...</p>';
        echo '<div class="info-box">Rs ' . htmlspecialchars($jobDetails['salary_from']) . "-" . htmlspecialchars($jobDetails['salary_to']) . '</div>';
        echo '<div class="info-box">' . htmlspecialchars($jobDetails['city']) . ", " . htmlspecialchars($jobDetails['country']) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        $recommendedCount++;
    }
}
            if ($recommendedCount === 0) {
                echo '<p>No job recommendations available at the moment.</p>';
            }
            ?>
                </div>
            </div>
        </div>
    </section>


    <?php include '../Assets/footer.php'; ?>
</body>

</html>