<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/Navigation.css">
    <link rel="stylesheet" href="css/footer.css">

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
                <li><a href="index.php">Home</a></li>
                <li><a href="category.php">Category</a></li>
                <li><a href="job.php">Jobs</a></li>
                <li><a href=" login.php">Login</a></li>
            </ul>
        </div>
    </nav>