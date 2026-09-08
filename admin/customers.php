<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";

$search = trim($_GET["search"] ?? "");

/* CUSTOMER STATISTICS */

$total_customers = 0;
$recent_customers = 0;

$count_query = $conn->query(
    "SELECT
        COUNT(*) AS total_customers,
        SUM(id >= (SELECT COALESCE(MAX(id), 0) - 4 FROM users WHERE role = 'customer')) AS recent_customers
     FROM users
     WHERE role = 'customer'"
);

if ($count_query) {
    $count_data = $count_query->fetch_assoc();

    $total_customers = (int) ($count_data["total_customers"] ?? 0);
    $recent_customers = (int) ($count_data["recent_customers"] ?? 0);
}

/* CUSTOMER QUERY */

$sql = "
    SELECT
        id,
        name,
        email
    FROM users
    WHERE role = 'customer'
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= "
        AND (
            name LIKE ?
            OR email LIKE ?
            OR id LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types = "sss";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $customers = $stmt->get_result();

} else {

    $customers = false;
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

    <title>Customers | Cafelia Admin</title>

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
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --espresso: #2b1710;
            --espresso-dark: #1d0e09;
            --coffee: #5b3425;
            --caramel: #b9824f;
            --cream: #f7f1e8;
            --cream-dark: #eee3d4;
            --white: #ffffff;
            --text: #30211b;
            --muted: #8b7b70;
            --border: #eadfd3;
            --green: #3f7656;
            --shadow: 0 18px 45px rgba(43, 23, 16, .08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: #f6f1ea;
            color: var(--text);
            font-family: "DM Sans", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* MAIN CONTENT */

        .main {
            margin-left: 260px;
            min-height: 100vh;
            padding: 34px 38px 50px;
        }

        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 30px;
        }

        .eyebrow {
            margin-bottom: 7px;
            color: var(--caramel);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .topbar h1 {
            color: var(--espresso);
            font-family: "Playfair Display", serif;
            font-size: clamp(30px, 3vw, 43px);
            line-height: 1.1;
        }

        .topbar p {
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 14px 9px 9px;
            background: rgba(255,255,255,.7);
            border: 1px solid var(--border);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(43,23,16,.04);
        }

        .avatar {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: var(--espresso);
            color: white;
            font-size: 13px;
            font-weight: 700;
        }

        .admin-user small {
            display: block;
            margin-bottom: 2px;
            color: var(--muted);
            font-size: 10px;
        }

        .admin-user strong {
            font-size: 12px;
        }

        /* STATS */

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 24px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            padding: 21px;
            background: rgba(255,255,255,.84);
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: var(--shadow);
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -25px;
            bottom: -30px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(185,130,79,.08);
        }

        .stat-title {
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .10em;
            text-transform: uppercase;
        }

        .stat-value {
            margin-top: 8px;
            color: var(--espresso);
            font-family: "Playfair Display", serif;
            font-size: 30px;
        }

        .stat-meta {
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
        }

        .stat-card.green .stat-value {
            color: var(--green);
        }

        /* CARD */

        .card {
            overflow: hidden;
            background: rgba(255,255,255,.87);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .card-head {
            padding: 23px 24px;
            border-bottom: 1px solid var(--border);
        }

        .card-title h2 {
            color: var(--espresso);
            font-family: "Playfair Display", serif;
            font-size: 22px;
        }

        .card-title p {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        /* SEARCH */

        .search-form {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            height: 44px;
            padding: 0 15px 0 39px;
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
            background: #fffdfa;
            color: var(--text);
            font-size: 12px;
            transition: .2s ease;
        }

        .search-box input:focus {
            border-color: var(--caramel);
            box-shadow: 0 0 0 3px rgba(185,130,79,.10);
        }

        .search-btn {
            height: 44px;
            padding: 0 20px;
            border: 0;
            border-radius: 11px;
            background: var(--espresso);
            color: white;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            transition: .2s ease;
        }

        .search-btn:hover {
            background: var(--coffee);
            transform: translateY(-1px);
        }

        .clear-btn {
            display: flex;
            align-items: center;
            height: 44px;
            padding: 0 17px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: white;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        .clear-btn:hover {
            color: var(--espresso);
        }

        /* TABLE */

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
        }

        thead {
            background: #faf6f0;
        }

        th {
            padding: 14px 22px;
            border-bottom: 1px solid var(--border);
            color: #9b8b7f;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .10em;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            padding: 17px 22px;
            border-bottom: 1px solid #f0e8df;
            vertical-align: middle;
            font-size: 12px;
        }

        tbody tr {
            transition: .18s ease;
        }

        tbody tr:hover {
            background: #fdfaf6;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .customer-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            padding: 7px 10px;
            border-radius: 9px;
            background: #f2e8dc;
            color: var(--espresso);
            font-size: 11px;
            font-weight: 800;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .customer-avatar {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: var(--cream-dark);
            color: var(--coffee);
            font-size: 12px;
            font-weight: 800;
        }

        .customer-name {
            color: var(--espresso);
            font-weight: 700;
        }

        .customer-label {
            margin-top: 3px;
            color: var(--muted);
            font-size: 10px;
        }

        .email {
            color: #66584f;
            font-size: 12px;
        }

        .account-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border: 1px solid #cfe3d5;
            border-radius: 9px;
            background: #eaf5ed;
            color: var(--green);
            font-size: 10px;
            font-weight: 800;
        }

        .account-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        /* EMPTY */

        .empty {
            padding: 65px 25px;
            text-align: center;
        }

        .empty-icon {
            width: 60px;
            height: 60px;
            display: grid;
            place-items: center;
            margin: 0 auto 15px;
            border-radius: 18px;
            background: var(--cream);
            font-size: 23px;
        }

        .empty h3 {
            color: var(--espresso);
            font-family: "Playfair Display", serif;
            font-size: 20px;
        }

        .empty p {
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
        }

        /* RESPONSIVE */

        @media (max-width: 1000px) {

            .stats {
                grid-template-columns: 1fr 1fr;
            }

        }

        @media (max-width: 900px) {

            .nav {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
            }

            .main {
                margin-left: 0;
                padding: 25px 20px 40px;
            }

        }

        @media (max-width: 650px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
            }

            .admin-user {
                width: 100%;
            }

            .search-form {
                flex-direction: column;
            }

            .search-btn,
            .clear-btn {
                width: 100%;
                justify-content: center;
            }

            .nav {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 430px) {

            .main {
                padding: 20px 14px 35px;
            }

            .card-head {
                padding: 18px;
            }

        }

    </style>

</head>

<body>


<?php
    $active_admin_page = "customers";
    include "sidebar.php";
?>

<!-- MAIN -->

<main class="main">


    <!-- HEADER -->

    <header class="topbar">

        <div>

            <div class="eyebrow">
                Cafelia Administration
            </div>

            <h1>
                Customers
            </h1>

            <p>
                Manage and view registered Cafelia customers.
            </p>

        </div>


        <div class="admin-user">

            <div class="avatar">

                <?php
                echo strtoupper(
                    substr($admin_name, 0, 1)
                );
                ?>

            </div>

            <div>

                <small>
                    Signed in as
                </small>

                <strong>
                    <?php
                    echo htmlspecialchars($admin_name);
                    ?>
                </strong>

            </div>

        </div>

    </header>


    <!-- STATISTICS -->

    <section class="stats">


        <div class="stat-card">

            <div class="stat-title">
                Total Customers
            </div>

            <div class="stat-value">
                <?php echo $total_customers; ?>
            </div>

            <div class="stat-meta">
                Registered customer accounts
            </div>

        </div>


        <div class="stat-card green">

            <div class="stat-title">
                Customer Accounts
            </div>

            <div class="stat-value">
                <?php echo $total_customers; ?>
            </div>

            <div class="stat-meta">
                Active customer records
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Latest Customers
            </div>

            <div class="stat-value">
                <?php echo $recent_customers; ?>
            </div>

            <div class="stat-meta">
                Most recently registered
            </div>

        </div>


    </section>


    <!-- CUSTOMER CARD -->

    <section class="card">


        <div class="card-head">


            <div class="card-title">

                <h2>
                    Customer Directory
                </h2>

                <p>
                    Search registered customers by name, email, or ID.
                </p>

            </div>


            <form
                method="GET"
                action="customers.php"
                class="search-form"
            >

                <div class="search-box">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search customer..."
                        value="<?php
                        echo htmlspecialchars($search);
                        ?>"
                    >

                </div>


                <button
                    type="submit"
                    class="search-btn"
                >
                    Search
                </button>


                <?php if ($search !== ""): ?>

                    <a
                        href="customers.php"
                        class="clear-btn"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </form>


        </div>


        <div class="table-wrap">


            <?php if ($customers && $customers->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Email Address
                            </th>

                            <th>
                                Account
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($customer = $customers->fetch_assoc()): ?>

                        <?php

                        $customer_name =
                            trim($customer["name"]);

                        $initial =
                            strtoupper(
                                substr(
                                    $customer_name,
                                    0,
                                    1
                                )
                            );

                        ?>


                        <tr>


                            <td>

                                <span class="customer-id">

                                    #<?php
                                    echo (int) $customer["id"];
                                    ?>

                                </span>

                            </td>


                            <td>

                                <div class="customer-info">


                                    <div class="customer-avatar">

                                        <?php
                                        echo htmlspecialchars(
                                            $initial
                                        );
                                        ?>

                                    </div>


                                    <div>

                                        <div class="customer-name">

                                            <?php
                                            echo htmlspecialchars(
                                                $customer_name
                                            );
                                            ?>

                                        </div>

                                        <div class="customer-label">
                                            Cafelia Customer
                                        </div>

                                    </div>


                                </div>

                            </td>


                            <td>

                                <div class="email">

                                    <?php
                                    echo htmlspecialchars(
                                        $customer["email"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <span class="account-badge">

                                    <span class="account-dot"></span>

                                    Customer

                                </span>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="empty">

                    <div class="empty-icon">
                        ♙
                    </div>

                    <h3>
                        No customers found
                    </h3>

                    <p>

                        <?php if ($search !== ""): ?>

                            Try another search term.

                        <?php else: ?>

                            Registered customers will appear here.

                        <?php endif; ?>

                    </p>

                </div>


            <?php endif; ?>


        </div>


    </section>


</main>

</body>
</html>