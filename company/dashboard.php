<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/dashboard.css">
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
    <section class="hero">
        <div class="hero-container">
            <div class="hero-left">
                <div class="hero-text">
                    <h2>Build your <br> dream team for <br> unparalleled success.</h2>
                </div>
                <button class="search-button">
                    <a style="font-size: 20px; color: white; font-family: Georgia; font-weight: normal; text-decoration:none;"
                        href="postjob.php"> Post a Job </a>
                </button>
            </div>

            <div class="hero-image">
                <img src="../Banners/banner4.png" alt="Career Image">
            </div>
        </div>
    </section>
    <section class="steps-container">
        <div class="step">
            <i class="fa fa-user-circle" aria-hidden="true"></i>
            <div class="step-text">
                <h2>Register Your Account</h2>
                <p>Start your journey by creating an account to unlock endless opportunities. Signing up is quick and
                    easy, giving you access to top industry jobs in minutes.</p>
            </div>
        </div>
        <div class="step">
            <i class="fa fa-upload" aria-hidden="true"></i>
            <div class="step-text">
                <h2>Upload Your Profile</h2>
                <p>Showcase your experience and skills by uploading your professional profile. The more detailed, the
                    better—make sure you're standing out to employers.</p>
            </div>
        </div>
        <div class="step">
            <i class="fa fa-briefcase" aria-hidden="true"></i>
            <div class="step-text">
                <h2>Post Your Jobs</h2>
                <p>Find the best talent for your company by posting job openings. Connect with qualified professionals
                    who fit your needs and make hiring seamless.</p>
            </div>
        </div>
    </section>

    <section class="hero1"></section>
    <?php include '../Assets/footer.php'; ?>
</body>

</html>