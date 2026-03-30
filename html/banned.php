<?php
// banned.php
// Shown when a user is banned. Accessible without being logged in
// (banned users are logged out before being sent here).
// A logged-in non-banned user visiting this URL is bounced away.

session_start();

// If somehow a non-banned logged-in user lands here, send them home
if (isset($_SESSION['user_id']) && empty($_SESSION['is_banned'])) {
    header('Location: index.php');
    exit();
}

// Clear any lingering session data so the user is fully logged out
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Account Suspended – Singapore Singles Society</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-pink: #FF4A7A;
                --text-dark: #2A1A22;
            }

            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background: #FFFFFF;
                color: var(--text-dark);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
                position: relative;
                overflow: hidden;
            }

            body::before {
                content: '';
                position: fixed;
                bottom: -10%;
                left: -10%;
                width: 600px;
                height: 600px;
                background: radial-gradient(circle, rgba(255,74,122,0.12) 0%, transparent 70%);
                z-index: 0;
                pointer-events: none;
            }

            body::after {
                content: '';
                position: fixed;
                top: 10%;
                right: -5%;
                width: 700px;
                height: 700px;
                background: radial-gradient(circle, rgba(255,74,122,0.10) 0%, transparent 70%);
                z-index: 0;
                pointer-events: none;
            }

            .ban-wrap {
                position: relative;
                z-index: 1;
                text-align: center;
                max-width: 480px;
                width: 100%;
            }

            /* Brand logo */
            .ban-brand {
                font-size: 2rem;
                font-weight: 800;
                color: var(--primary-pink);
                font-style: italic;
                text-decoration: none;
                display: inline-block;
                margin-bottom: 2.5rem;
            }

            /* Icon circle */
            .ban-icon-wrap {
                width: 88px;
                height: 88px;
                border-radius: 50%;
                background: linear-gradient(135deg, #FFF0F5 0%, #FFE0EC 100%);
                border: 2px solid rgba(255, 74, 122, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.75rem;
                animation: iconPulse 3s ease-in-out infinite;
            }

            @keyframes iconPulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(255,74,122,0.15); }
                50%       { box-shadow: 0 0 0 14px rgba(255,74,122,0); }
            }

            .ban-icon {
                font-size: 2.4rem;
                line-height: 1;
            }

            /* Card */
            .ban-card {
                background: #FFFFFF;
                border: 1.5px solid rgba(255,74,122,0.1);
                border-radius: 24px;
                padding: 2.5rem 2rem;
                box-shadow: 0 12px 40px rgba(255,74,122,0.07), 0 2px 8px rgba(0,0,0,0.04);
            }

            .ban-eyebrow {
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.14em;
                color: var(--primary-pink);
                margin-bottom: 0.65rem;
            }

            .ban-heading {
                font-size: 1.75rem;
                font-weight: 800;
                color: var(--text-dark);
                line-height: 1.2;
                margin-bottom: 1rem;
                letter-spacing: -0.3px;
            }

            .ban-body {
                font-size: 0.95rem;
                color: #666;
                line-height: 1.7;
                margin-bottom: 1.75rem;
            }

            /* Divider */
            .ban-divider {
                height: 1px;
                background: #F0F0F0;
                margin: 1.5rem 0;
            }

            /* Appeal info box */
            .ban-appeal {
                background: #FAFAFA;
                border-radius: 12px;
                padding: 1rem 1.1rem;
                font-size: 0.85rem;
                color: #888;
                line-height: 1.6;
                text-align: left;
                display: flex;
                gap: 0.75rem;
                align-items: flex-start;
            }

            .ban-appeal-icon {
                font-size: 1.1rem;
                flex-shrink: 0;
                margin-top: 0.05rem;
            }

            .ban-appeal a {
                color: var(--primary-pink);
                font-weight: 700;
                text-decoration: none;
            }

            .ban-appeal a:hover { text-decoration: underline; }

            /* Footer note */
            .ban-footer {
                margin-top: 2rem;
                font-size: 0.78rem;
                color: #ccc;
            }

            .ban-footer a {
                color: #bbb;
                text-decoration: none;
                font-weight: 600;
            }

            .ban-footer a:hover { color: var(--primary-pink); }
        </style>
    </head>
    <body>

    <main class="ban-wrap">

        <a href="index.php" class="ban-brand" aria-label="Singapore Singles Society home">S³</a>

        <div class="ban-card">
            <p class="ban-eyebrow">Account Suspended</p>
            <h1 class="ban-heading">Your account has been suspended</h1>
            <p class="ban-body">
                Your account has been suspended following a review of activity that
                violated our community guidelines. You are no longer able to access
                Singapore Singles Society.
            </p>

            <div class="ban-divider"></div>

            <div class="ban-appeal">
                If you believe this is a mistake, you can contact our support team at
                <a href="mailto:support@s3.local">support@s3.local</a> to appeal this decision.
                Please include your registered email address in your message.
            </div>
        </div>

        <p class="ban-footer">
            <a href="index.php">Return to homepage</a>
            &nbsp;·&nbsp;
            <a href="about.php">About S³</a>
        </p>

    </main>

    </body>
</html>