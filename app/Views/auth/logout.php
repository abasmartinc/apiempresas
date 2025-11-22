<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Logged out · Documents Generator</title>

    <link rel="shortcut icon" href="<?=getenv('ASSETS')?>img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" type="image/x-icon" href="<?=getenv('ASSETS')?>img/apple-touch-icon-57x57-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72" href="<?=getenv('ASSETS')?>img/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href="<?=getenv('ASSETS')?>img/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href="<?=getenv('ASSETS')?>img/apple-touch-icon-144x144-precomposed.png">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?=getenv('ASSETS')?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=getenv('ASSETS')?>css/menu.css" rel="stylesheet">
    <link href="<?=getenv('ASSETS')?>css/style.css" rel="stylesheet">
    <link href="<?=getenv('ASSETS')?>css/vendors.css" rel="stylesheet">
    <link href="<?=getenv('ASSETS')?>css/login.css" rel="stylesheet">
    <script src="<?=getenv('ASSETS')?>js/modernizr.js"></script>
</head>
<body class="dg-login">

<main class="dg-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-5">


                <!-- Card -->
                <section class="dg-card shadow-lg">
                    <div class="dg-card-header text-center">
                        <div class="dg-app-avatar" style="background: #16a34a;">
                            <!-- Check icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                        <h1 class="dg-title">You’ve signed out</h1>
                        <p class="dg-subtitle">Your session has ended safely.</p>
                    </div>

                    <div class="p-4 text-center">
                        <div class="alert alert-success mb-4" role="alert">
                            <strong>Success:</strong> You are now logged out.
                        </div>

                        <p class="mb-4 dg-muted">
                            You can close this window, go back to the homepage, or sign in again.
                        </p>

                        <div class="d-grid gap-2">
                            <a href="<?=site_url()?>" class="dg-btn dg-btn-primary w-100">
                                <span>Sign in again</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="ms-2">
                                    <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>

                        <p class="mt-4 small dg-muted">
                            Redirecting to login in <span id="dg-countdown">5</span> seconds…
                        </p>
                    </div>
                </section>

                <!-- Footer -->
                <footer class="dg-footer text-center">
                    <small class="dg-muted">
                        © <?=date('Y')?> Assessments Generator by
                        <a href="https://abasmart.net" target="_blank" class="dg-link">ABASmart</a> ·
                        <a href="#" class="dg-link">Privacy</a> ·
                        <a href="#" class="dg-link">Cookie settings</a>
                    </small>
                </footer>

            </div>
        </div>
    </div>
</main>

<script>
    // Simple countdown + redirect (adjust target or disable if not desired)
    (function() {
        var seconds = 5; // change delay here
        var el = document.getElementById('dg-countdown');
        var timer = setInterval(function() {
            seconds--;
            if (el) el.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = "<?=site_url()?>";
            }
        }, 1000);
    })();
</script>
</body>
</html>
