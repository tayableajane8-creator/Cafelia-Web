<?php
session_start();

require_once "../config/database.php";

/* ---------------------------------------------------------
   ADMIN ACCESS
--------------------------------------------------------- */
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$active_admin_page = "messages";

$success = "";
$error = "";
$search = trim($_GET["search"] ?? "");

/* ---------------------------------------------------------
   DELETE MESSAGE
--------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "delete") {

        $message_id = (int)($_POST["id"] ?? 0);

        if ($message_id <= 0) {
            $error = "Invalid message.";
        } else {

            $delete = $conn->prepare(
                "DELETE FROM contact_messages WHERE id = ? LIMIT 1"
            );

            if ($delete) {

                $delete->bind_param("i", $message_id);

                if ($delete->execute()) {
                    if ($delete->affected_rows > 0) {
                        header("Location: messages.php?deleted=1");
                        exit();
                    }

                    $error = "Message was not found.";
                } else {
                    $error = "Unable to delete the message.";
                }

                $delete->close();

            } else {
                $error = "Unable to process the delete request.";
            }
        }
    }
}

/* ---------------------------------------------------------
   SUCCESS MESSAGE
--------------------------------------------------------- */
if (isset($_GET["deleted"]) && $_GET["deleted"] === "1") {
    $success = "Message deleted successfully.";
}

/* ---------------------------------------------------------
   GET MESSAGES
--------------------------------------------------------- */
$messages = [];

if ($search !== "") {

    $stmt = $conn->prepare(
        "SELECT id, name, email, subject, message, created_at
         FROM contact_messages
         WHERE name LIKE ?
            OR email LIKE ?
            OR subject LIKE ?
            OR message LIKE ?
         ORDER BY created_at DESC, id DESC"
    );

    if ($stmt) {

        $term = "%" . $search . "%";

        $stmt->bind_param(
            "ssss",
            $term,
            $term,
            $term,
            $term
        );

        if ($stmt->execute()) {

            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

        } else {
            $error = "Unable to load customer messages.";
        }

        $stmt->close();

    } else {
        $error = "Unable to load customer messages.";
    }

} else {

    $result = $conn->query(
        "SELECT id, name, email, subject, message, created_at
         FROM contact_messages
         ORDER BY created_at DESC, id DESC"
    );

    if ($result) {

        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

    } else {
        $error = "Unable to load customer messages.";
    }
}

/* ---------------------------------------------------------
   COUNTERS
--------------------------------------------------------- */
$total_messages = 0;
$today_messages = 0;

$count_result = $conn->query(
    "SELECT COUNT(*) AS total FROM contact_messages"
);

if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $total_messages = (int)($count_row["total"] ?? 0);
}

$today_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM contact_messages
     WHERE DATE(created_at) = CURDATE()"
);

if ($today_result) {
    $today_row = $today_result->fetch_assoc();
    $today_messages = (int)($today_row["total"] ?? 0);
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

    <title>Messages | Cafelia Admin</title>

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
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --espresso: #281913;
            --coffee: #5b3827;
            --caramel: #c99a68;
            --cream: #f7f0e7;
            --cream-light: #fcfaf6;
            --white: #ffffff;
            --text: #34251d;
            --muted: #8b796c;
            --border: rgba(77, 51, 36, .11);
            --success-bg: #eaf4ed;
            --success-text: #386447;
            --danger-bg: #f9e9e7;
            --danger-text: #9b4b42;
            --sidebar-width: 260px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            background: var(--cream);
            color: var(--text);
            font-family: "DM Sans", sans-serif;
            overflow-x: hidden;
        }

        button,
        input {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        a {
            text-decoration: none;
        }

        /* -------------------------------------------------
           MAIN CONTENT
        ------------------------------------------------- */

        .messages-page {
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            padding: 38px 42px 60px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .eyebrow {
            margin-bottom: 8px;
            color: var(--caramel);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .page-header h1 {
            margin: 0;
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 42px;
            line-height: 1.1;
        }

        .page-header p {
            max-width: 680px;
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        /* -------------------------------------------------
           ALERTS
        ------------------------------------------------- */

        .alert {
            margin-bottom: 22px;
            padding: 13px 16px;
            border-radius: 11px;
            font-size: 12px;
            font-weight: 700;
        }

        .alert.success {
            background: var(--success-bg);
            border: 1px solid #cce2d2;
            color: var(--success-text);
        }

        .alert.error {
            background: var(--danger-bg);
            border: 1px solid #eccdca;
            color: var(--danger-text);
        }

        /* -------------------------------------------------
           STATS
        ------------------------------------------------- */

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 22px 24px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: rgba(255,255,255,.84);
            box-shadow: 0 15px 40px rgba(49,30,20,.06);
        }

        .stat-label {
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .stat-number {
            margin-top: 7px;
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 31px;
            font-weight: 700;
        }

        .stat-note {
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
        }

        /* -------------------------------------------------
           MESSAGE CARD
        ------------------------------------------------- */

        .message-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 19px;
            background: rgba(255,255,255,.9);
            box-shadow: 0 18px 45px rgba(49,30,20,.07);
        }

        .message-header {
            padding: 22px 24px;
            border-bottom: 1px solid var(--border);
        }

        .message-header h2 {
            margin: 0;
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 22px;
        }

        .message-header p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        /* -------------------------------------------------
           SEARCH
        ------------------------------------------------- */

        .search-form {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .search-input {
            width: 100%;
            height: 44px;
            flex: 1;
            padding: 0 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            outline: none;
            background: #fffdfa;
            color: var(--text);
            font-size: 12px;
        }

        .search-input:focus {
            border-color: var(--caramel);
            box-shadow: 0 0 0 3px rgba(201,154,104,.12);
        }

        .search-button,
        .clear-button {
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 17px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .search-button {
            border: 0;
            background: var(--espresso);
            color: #fff;
        }

        .search-button:hover {
            background: var(--coffee);
        }

        .clear-button {
            border: 1px solid var(--border);
            background: #fff;
            color: var(--muted);
        }

        /* -------------------------------------------------
           TABLE
        ------------------------------------------------- */

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 950px;
            border-collapse: collapse;
        }

        thead {
            background: #faf6f0;
        }

        th {
            padding: 14px 19px;
            border-bottom: 1px solid var(--border);
            color: #9a897d;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .09em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 17px 19px;
            border-bottom: 1px solid #f0e8df;
            vertical-align: middle;
            font-size: 12px;
        }

        tbody tr:hover {
            background: #fdfaf6;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .id-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 45px;
            padding: 7px 9px;
            border-radius: 8px;
            background: #f1e8dd;
            color: var(--espresso);
            font-size: 10px;
            font-weight: 800;
        }

        .sender-name {
            color: var(--espresso);
            font-weight: 800;
        }

        .sender-email {
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
            word-break: break-word;
        }

        .subject {
            max-width: 230px;
            color: var(--espresso);
            font-weight: 700;
            line-height: 1.45;
        }

        .preview {
            max-width: 340px;
            color: #74665d;
            line-height: 1.55;
        }

        .received {
            color: #66584e;
            font-size: 11px;
            line-height: 1.55;
            white-space: nowrap;
        }

        /* -------------------------------------------------
           ACTIONS
        ------------------------------------------------- */

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .action-button {
            min-height: 35px;
            padding: 0 11px;
            border: 0;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 800;
        }

        .view-button {
            background: #e9f1ed;
            color: #35634d;
        }

        .view-button:hover {
            background: #dcebe3;
        }

        .delete-button {
            background: #f8e8e6;
            color: #a34b43;
        }

        .delete-button:hover {
            background: #f1dad7;
        }

        .delete-form {
            margin: 0;
        }

        /* -------------------------------------------------
           EMPTY STATE
        ------------------------------------------------- */

        .empty {
            padding: 70px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 60px;
            height: 60px;
            display: grid;
            place-items: center;
            margin: 0 auto 14px;
            border-radius: 17px;
            background: var(--cream);
            font-size: 24px;
        }

        .empty strong {
            display: block;
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 21px;
        }

        .empty p {
            max-width: 420px;
            margin: 7px auto 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
        }

        /* -------------------------------------------------
           MODAL
        ------------------------------------------------- */

        .modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(28,15,9,.62);
        }

        .modal.show {
            display: flex;
        }

        .modal-box {
            width: min(680px, 100%);
            max-height: 90vh;
            overflow-y: auto;
            padding: 26px;
            border-radius: 18px;
            background: var(--cream-light);
            box-shadow: 0 25px 70px rgba(0,0,0,.25);
        }

        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 25px;
            line-height: 1.2;
        }

        .close-button {
            width: 35px;
            height: 35px;
            display: grid;
            place-items: center;
            flex: 0 0 35px;
            border: 0;
            border-radius: 50%;
            background: #eee5d9;
            color: var(--coffee);
            font-size: 19px;
        }

        .meta {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e8ddd1;
            border-radius: 12px;
            background: #f8f3eb;
        }

        .meta-row {
            display: flex;
            gap: 13px;
            margin-bottom: 9px;
            font-size: 12px;
        }

        .meta-row:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            width: 70px;
            flex: 0 0 70px;
            color: #99897d;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .meta-value {
            color: var(--text);
            word-break: break-word;
        }

        .full-message {
            margin-top: 17px;
            min-height: 130px;
            padding: 17px;
            border: 1px solid #e4dacf;
            border-radius: 12px;
            background: #fff;
            color: #4f433b;
            font-size: 13px;
            line-height: 1.75;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 17px;
        }

        .modal-close-text {
            min-height: 38px;
            padding: 0 16px;
            border: 0;
            border-radius: 9px;
            background: var(--espresso);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
        }

        /* -------------------------------------------------
           RESPONSIVE
        ------------------------------------------------- */

        @media (max-width: 900px) {
            .messages-page {
                margin-left: 0;
                padding: 30px 24px 50px;
            }
        }

        @media (max-width: 650px) {

            .messages-page {
                padding: 24px 15px 40px;
            }

            .page-header h1 {
                font-size: 34px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .search-form {
                flex-direction: column;
            }

            .search-button,
            .clear-button {
                width: 100%;
            }

            .message-header {
                padding: 20px;
            }

            .modal-box {
                padding: 21px;
            }
        }

    </style>

</head>

<body>

<?php include "sidebar.php"; ?>

<main class="messages-page">

    <header class="page-header">

        <div class="eyebrow">
            Cafelia Administration
        </div>

        <h1>
            Customer Messages
        </h1>

        <p>
            View, search, read, and manage messages submitted
            through the Cafelia Contact Us page.
        </p>

    </header>


    <?php if ($success !== ""): ?>

        <div class="alert success" role="status">
            ✓ <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="alert error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>

    <?php endif; ?>


    <section class="stats">

        <div class="stat-card">

            <div class="stat-label">
                Total Messages
            </div>

            <div class="stat-number">
                <?= number_format($total_messages) ?>
            </div>

            <div class="stat-note">
                All customer contact submissions
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Received Today
            </div>

            <div class="stat-number">
                <?= number_format($today_messages) ?>
            </div>

            <div class="stat-note">
                Messages submitted today
            </div>

        </div>

    </section>


    <section class="message-card">

        <div class="message-header">

            <h2>
                Inbox
            </h2>

            <p>
                <?= count($messages) ?>
                message<?= count($messages) === 1 ? "" : "s" ?>
                shown
                <?php if ($search !== ""): ?>
                    for
                    "<strong><?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?></strong>"
                <?php endif; ?>
            </p>


            <form
                class="search-form"
                method="GET"
                action="messages.php"
            >

                <input
                    class="search-input"
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>"
                    placeholder="Search name, email, subject, or message..."
                    autocomplete="off"
                >

                <button
                    type="submit"
                    class="search-button"
                >
                    Search
                </button>

                <?php if ($search !== ""): ?>

                    <a
                        href="messages.php"
                        class="clear-button"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <div class="table-wrap">

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th>Actions</th>
                    </tr>

                </thead>


                <tbody>

                <?php if (empty($messages)): ?>

                    <tr>

                        <td colspan="6">

                            <div class="empty">

                                <div class="empty-icon">
                                    ✉
                                </div>

                                <strong>
                                    No messages found
                                </strong>

                                <p>
                                    <?php if ($search !== ""): ?>
                                        No customer messages matched your search.
                                    <?php else: ?>
                                        Messages submitted through the Contact Us
                                        page will appear here.
                                    <?php endif; ?>
                                </p>

                            </div>

                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($messages as $item): ?>

                        <?php

                        $id = (int)($item["id"] ?? 0);

                        $name = (string)($item["name"] ?? "");
                        $email = (string)($item["email"] ?? "");
                        $subject = (string)($item["subject"] ?? "");
                        $message = (string)($item["message"] ?? "");
                        $created_at = (string)($item["created_at"] ?? "");

                        $preview = trim(
                            preg_replace(
                                "/\s+/",
                                " ",
                                $message
                            )
                        );

                        if (strlen($preview) > 110) {
                            $preview =
                                substr($preview, 0, 110) . "...";
                        }

                        $date_display = "—";

                        if ($created_at !== "") {

                            $time = strtotime($created_at);

                            if ($time !== false) {

                                $date_display =
                                    date("M d, Y", $time)
                                    . "<br>"
                                    . date("h:i A", $time);
                            }
                        }

                        ?>

                        <tr>

                            <td>
                                <span class="id-badge">
                                    #<?= $id ?>
                                </span>
                            </td>


                            <td>

                                <div class="sender-name">
                                    <?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>
                                </div>

                                <div class="sender-email">
                                    <?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>
                                </div>

                            </td>


                            <td>

                                <div class="subject">
                                    <?= htmlspecialchars($subject, ENT_QUOTES, "UTF-8") ?>
                                </div>

                            </td>


                            <td>

                                <div class="preview">
                                    <?= htmlspecialchars($preview, ENT_QUOTES, "UTF-8") ?>
                                </div>

                            </td>


                            <td>

                                <div class="received">
                                    <?= $date_display ?>
                                </div>

                            </td>


                            <td>

                                <div class="actions">

                                    <button
                                        type="button"
                                        class="action-button view-button"
                                        onclick="openMessage(
                                            <?= htmlspecialchars(json_encode($name), ENT_QUOTES, "UTF-8") ?>,
                                            <?= htmlspecialchars(json_encode($email), ENT_QUOTES, "UTF-8") ?>,
                                            <?= htmlspecialchars(json_encode($subject), ENT_QUOTES, "UTF-8") ?>,
                                            <?= htmlspecialchars(json_encode($message), ENT_QUOTES, "UTF-8") ?>,
                                            <?= htmlspecialchars(json_encode(strip_tags(str_replace("<br>", " ", $date_display))), ENT_QUOTES, "UTF-8") ?>
                                        )"
                                    >
                                        View
                                    </button>


                                    <form
                                        class="delete-form"
                                        method="POST"
                                        action="messages.php"
                                        onsubmit="return confirmDelete();"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $id ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="action-button delete-button"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>


<!-- ---------------------------------------------------------
     VIEW MESSAGE MODAL
---------------------------------------------------------- -->

<div
    class="modal"
    id="messageModal"
    aria-hidden="true"
>

    <div
        class="modal-box"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalSubject"
    >

        <div class="modal-header">

            <h3 id="modalSubject">
                Message
            </h3>

            <button
                type="button"
                class="close-button"
                onclick="closeMessage()"
                aria-label="Close"
            >
                ×
            </button>

        </div>


        <div class="meta">

            <div class="meta-row">
                <span class="meta-label">From</span>
                <span class="meta-value" id="modalName"></span>
            </div>

            <div class="meta-row">
                <span class="meta-label">Email</span>
                <span class="meta-value" id="modalEmail"></span>
            </div>

            <div class="meta-row">
                <span class="meta-label">Received</span>
                <span class="meta-value" id="modalDate"></span>
            </div>

        </div>


        <div
            class="full-message"
            id="modalMessage"
        ></div>


        <div class="modal-footer">

            <button
                type="button"
                class="modal-close-text"
                onclick="closeMessage()"
            >
                Close
            </button>

        </div>

    </div>

</div>


<script>

const modal = document.getElementById("messageModal");


function openMessage(
    name,
    email,
    subject,
    message,
    date
) {

    document.getElementById("modalName").textContent =
        name || "";

    document.getElementById("modalEmail").textContent =
        email || "";

    document.getElementById("modalSubject").textContent =
        subject || "Message";

    document.getElementById("modalMessage").textContent =
        message || "";

    document.getElementById("modalDate").textContent =
        date || "";

    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");

    document.body.style.overflow = "hidden";
}


function closeMessage() {

    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");

    document.body.style.overflow = "";
}


function confirmDelete() {

    return confirm(
        "Are you sure you want to delete this customer message?"
    );
}


modal.addEventListener("click", function(event) {

    if (event.target === modal) {
        closeMessage();
    }

});


document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {
        closeMessage();
    }

});

</script>

</body>
</html>
