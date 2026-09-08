<?php

session_start();
require_once "config/database.php";

$error = "";

/* ==========================================
   ALREADY LOGGED IN
========================================== */

if (isset($_SESSION["admin_id"])) {
    header("Location: admin/dashboard.php");
    exit();
}

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}


/* ==========================================
   LOGIN PROCESS
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* ======================================
           CHECK ADMIN ACCOUNT
        ====================================== */

        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM admin_users
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {

                $admin = $result->fetch_assoc();

                if (password_verify($password, $admin["password"])) {

                    session_regenerate_id(true);

                    /* Remove customer session */
                    unset(
                        $_SESSION["user_id"],
                        $_SESSION["user_name"],
                        $_SESSION["user_email"],
                        $_SESSION["user_role"]
                    );

                    /* Create admin session */
                    $_SESSION["admin_id"] = $admin["id"];
                    $_SESSION["admin_name"] = $admin["name"];
                    $_SESSION["admin_email"] = $admin["email"];

                    header("Location: admin/dashboard.php");
                    exit();

                } else {

                    $error = "Incorrect email or password.";
                }

                $stmt->close();

            } else {

                $stmt->close();


                /* ======================================
                   CHECK CUSTOMER ACCOUNT
                ====================================== */

                $stmt = $conn->prepare(
                    "SELECT id, name, email, password
                     FROM users
                     WHERE email = ?
                     LIMIT 1"
                );

                if ($stmt) {

                    $stmt->bind_param("s", $email);
                    $stmt->execute();

                    $result = $stmt->get_result();

                    if ($result && $result->num_rows === 1) {

                        $user = $result->fetch_assoc();

                        if (password_verify($password, $user["password"])) {

                            session_regenerate_id(true);

                            /* Remove admin session */
                            unset(
                                $_SESSION["admin_id"],
                                $_SESSION["admin_name"],
                                $_SESSION["admin_email"]
                            );

                            /* Create customer session */
                            $_SESSION["user_id"] = $user["id"];
                            $_SESSION["user_name"] = $user["name"];
                            $_SESSION["user_email"] = $user["email"];

                            header("Location: index.php");
                            exit();

                        } else {

                            $error = "Incorrect email or password.";
                        }

                    } else {

                        $error = "Incorrect email or password.";
                    }

                    $stmt->close();

                } else {

                    $error = "Database error.";
                }
            }

        } else {

            $error = "Database error.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Cafelia</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --espresso: #24150f;
            --espresso-2: #321d14;
            --coffee: #5a3827;
            --caramel: #b98252;
            --gold: #d8a36d;

            --cream: #f8f2e9;
            --white: #fffdf9;
            --muted: #79695d;

            --danger-bg: #fff0ec;
            --danger: #a94737;

            --border: rgba(91, 58, 39, .15);

            --shadow:
                0 24px 70px rgba(36, 21, 15, .16);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 34px 20px;

            font-family:
                "DM Sans",
                Arial,
                sans-serif;

            color: var(--espresso);

            background:
                radial-gradient(
                    circle at 15% 15%,
                    rgba(216, 163, 109, .18),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 85% 85%,
                    rgba(185, 130, 82, .13),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fbf6ee,
                    #f1e6d8,
                    #ead8c5
                );
        }

        .login-shell {
            width: min(1060px, 100%);
            min-height: 650px;

            display: grid;

            grid-template-columns: 1.02fr .98fr;

            overflow: hidden;

            border:
                1px solid rgba(255,255,255,.72);

            border-radius: 30px;

            background:
                rgba(255,253,249,.72);

            box-shadow: var(--shadow);

            backdrop-filter: blur(18px);
        }

        /* LEFT */

        .brand-panel {
            position: relative;

            padding: 54px;

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            overflow: hidden;

            color: var(--cream);

            background:
                linear-gradient(
                    145deg,
                    rgba(36,21,15,.98),
                    rgba(76,45,31,.97)
                );
        }

        .brand-panel::before,
        .brand-panel::after {
            content: "";

            position: absolute;

            border-radius: 50%;

            pointer-events: none;
        }

        .brand-panel::before {
            width: 310px;
            height: 310px;

            right: -120px;
            top: -90px;

            border:
                1px solid rgba(255,255,255,.09);
        }

        .brand-panel::after {
            width: 250px;
            height: 250px;

            left: -130px;
            bottom: -100px;

            border:
                1px solid rgba(216,163,109,.15);
        }

        .brand-content,
        .brand-footer {
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            display: inline-block;

            color: #f7eadb;

            text-decoration: none;

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 34px;
            font-weight: 700;

            letter-spacing: .13em;

            margin-bottom: 58px;
        }

        .eyebrow {
            margin-bottom: 16px;

            color: var(--gold);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: .24em;

            text-transform: uppercase;
        }

        .brand-title {
            max-width: 430px;

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: clamp(42px, 5vw, 66px);

            line-height: .98;

            letter-spacing: -.035em;

            font-weight: 600;
        }

        .brand-description {
            max-width: 390px;

            margin-top: 24px;

            color: rgba(248,242,233,.72);

            font-size: 14px;

            line-height: 1.8;
        }

        .coffee-mark {
            width: 94px;
            height: 94px;

            margin-top: 48px;

            display: grid;
            place-items: center;

            border:
                1px solid rgba(255,255,255,.16);

            border-radius: 50%;

            background:
                rgba(255,255,255,.06);

            font-size: 36px;
        }

        .brand-footer {
            color: rgba(248,242,233,.52);

            font-size: 11px;

            letter-spacing: .08em;

            text-transform: uppercase;
        }

        /* RIGHT */

        .login-panel {
            padding: 54px clamp(32px,5vw,72px);

            display: flex;
            align-items: center;

            background:
                rgba(255,253,249,.92);
        }

        .login-content {
            width: 100%;
            max-width: 420px;

            margin: auto;
        }

        .login-kicker {
            margin-bottom: 13px;

            color: var(--caramel);

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .22em;

            text-transform: uppercase;
        }

        .login-title {
            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: clamp(34px,4vw,48px);

            line-height: 1.05;

            font-weight: 600;

            letter-spacing: -.025em;
        }

        .login-subtitle {
            margin-top: 12px;

            color: var(--muted);

            font-size: 14px;

            line-height: 1.7;
        }

        .error-message {
            margin-top: 26px;

            padding: 13px 15px;

            border:
                1px solid rgba(169,71,55,.16);

            border-radius: 12px;

            background: var(--danger-bg);

            color: var(--danger);

            font-size: 13px;

            line-height: 1.5;
        }

        form {
            margin-top: 30px;
        }

        .form-group {
            margin-bottom: 21px;
        }

        label {
            display: block;

            margin-bottom: 9px;

            color: var(--espresso);

            font-size: 12px;
            font-weight: 700;
        }

        input {
            width: 100%;
            height: 54px;

            padding: 0 16px;

            border:
                1px solid var(--border);

            border-radius: 12px;

            outline: none;

            background: #fffefa;

            color: var(--espresso);

            font-family: inherit;

            font-size: 14px;

            transition: .2s ease;
        }

        input:focus {
            border-color: var(--caramel);

            box-shadow:
                0 0 0 4px rgba(185,130,82,.12);
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 54px;
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--caramel);
            cursor: pointer;
            transition: .2s ease;
        }

        .toggle-password:hover {
            background: rgba(185, 130, 82, .09);
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--caramel);
            outline-offset: 2px;
        }

        .toggle-password svg {
            width: 19px;
            height: 19px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .login-button {
            width: 100%;
            min-height: 54px;

            margin-top: 5px;

            border: 0;
            border-radius: 12px;

            background: var(--espresso);

            color: #fffaf3;

            font-family: inherit;

            font-size: 12px;
            font-weight: 800;

            letter-spacing: .16em;

            text-transform: uppercase;

            cursor: pointer;

            transition: .2s ease;

            box-shadow:
                0 12px 25px rgba(36,21,15,.17);
        }

        .login-button:hover {
            background: var(--coffee);

            transform: translateY(-2px);
        }

        .register-text {
            margin-top: 24px;

            text-align: center;

            color: var(--muted);

            font-size: 13px;
        }

        .register-text a {
            margin-left: 4px;

            color: var(--coffee);

            font-weight: 800;

            text-decoration: none;
        }

        .register-text a:hover {
            color: var(--caramel);
        }

        .back-home {
            display: block;

            width: fit-content;

            margin: 25px auto 0;

            color: #8b786a;

            font-size: 12px;
            font-weight: 600;

            text-decoration: none;
        }

        .back-home:hover {
            color: var(--coffee);
        }

        .secure-note {
            display: flex;
            justify-content: center;

            gap: 7px;

            margin-top: 27px;

            color: #a08f81;

            font-size: 10px;

            letter-spacing: .08em;

            text-transform: uppercase;
        }

        /* RESPONSIVE */

        @media (max-width: 820px) {

            .login-shell {
                grid-template-columns: 1fr;

                max-width: 560px;

                min-height: auto;

                border-radius: 24px;
            }

            .brand-panel {
                min-height: 300px;

                padding: 34px;
            }

            .brand-logo {
                margin-bottom: 38px;

                font-size: 29px;
            }

            .brand-title {
                font-size: 43px;
            }

            .brand-description,
            .coffee-mark {
                display: none;
            }

            .brand-footer {
                margin-top: 40px;
            }

            .login-panel {
                padding: 42px 34px 45px;
            }
        }

        @media (max-width: 480px) {

            body {
                padding: 12px;
            }

            .brand-panel {
                min-height: 255px;

                padding: 28px 24px;
            }

            .brand-logo {
                font-size: 25px;

                margin-bottom: 32px;
            }

            .brand-title {
                font-size: 38px;
            }

            .login-panel {
                padding: 34px 23px 38px;
            }

            .login-title {
                font-size: 36px;
            }

            input,
            .login-button {
                height: 52px;
            }
        }

    </style>

</head>

<body>

    <main class="login-shell">

        <section class="brand-panel">

            <div class="brand-content">

                <a
                    href="index.php"
                    class="brand-logo"
                >
                   <a href="index.php" class="logo"><img src="image/logo.png" alt="Cafelia"></a>
                </a>

                <div class="eyebrow">
                    Welcome to Cafelia
                </div>

                <h1 class="brand-title">
                    Your coffee moments start here.
                </h1>

                <p class="brand-description">
                    Sign in to continue your Cafelia experience —
                    from discovering your favorites to keeping
                    track of your orders.
                </p>

                <div class="coffee-mark">
                    ☕
                </div>

            </div>

            <div class="brand-footer">
                Coffee, treats, and good moments.
            </div>

        </section>


        <section class="login-panel">

            <div class="login-content">

                <div class="login-kicker">
                    Cafelia Account
                </div>

                <h2 class="login-title">
                    Welcome back.
                </h2>

                <p class="login-subtitle">
                    Login to your Cafelia account to continue.
                </p>


                <?php if ($error !== ""): ?>

                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <form
                    action="login.php"
                    method="POST"
                >

                    <div class="form-group">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?php
                                echo htmlspecialchars(
                                    $_POST["email"] ?? ""
                                );
                            ?>"
                            autocomplete="email"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>

                        <div class="password-field">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="toggle-password"
                                data-target="password"
                                aria-label="Show password"
                                aria-pressed="false"
                                title="Show password"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="2.5"></circle>
                                </svg>
                            </button>

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="login-button"
                    >
                        Login
                    </button>

                </form>


                <p class="register-text">

                    Don't have an account?

                    <a href="register.php">
                        Create Account
                    </a>

                </p>


                <a
                    href="index.php"
                    class="back-home"
                >
                    ← Back to Home
                </a>


                <div class="secure-note">
                    <span>●</span>
                    <span>Secure account access</span>
                </div>

            </div>

        </section>

    </main>

    <script>
        document.querySelectorAll(".toggle-password").forEach(function (button) {
            button.addEventListener("click", function () {
                const input = document.getElementById(
                    button.getAttribute("data-target")
                );

                if (!input) return;

                const isHidden = input.type === "password";
                input.type = isHidden ? "text" : "password";

                button.setAttribute(
                    "aria-label",
                    isHidden ? "Hide password" : "Show password"
                );
                button.setAttribute(
                    "title",
                    isHidden ? "Hide password" : "Show password"
                );
                button.setAttribute(
                    "aria-pressed",
                    isHidden ? "true" : "false"
                );

                button.innerHTML = isHidden
                    ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.6 6.2A10.8 10.8 0 0 1 12 6c6.5 0 10 6 10 6a18.2 18.2 0 0 1-3.1 3.8"></path><path d="M6.5 8.2C3.8 10 2 12 2 12s3.5 6 10 6c1.6 0 3-.3 4.2-.8"></path><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path></svg>'
                    : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>';
            });
        });
    </script>

</body>

</html>