/* =========================================
   CAFELIA ADMIN JAVASCRIPT
========================================= */


// =========================================
// CONFIRM DELETE
// =========================================

function confirmDelete(message) {

    if (!message) {
        message = "Are you sure you want to delete this item?";
    }

    return confirm(message);
}


// =========================================
// CONFIRM ORDER STATUS
// =========================================

function confirmStatusChange(status) {

    return confirm(
        "Are you sure you want to change the order status to " +
        status +
        "?"
    );

}


// =========================================
// IMAGE PREVIEW
// =========================================

function previewProductImage(input) {

    const preview =
        document.getElementById("imagePreview");

    if (
        !preview ||
        !input.files ||
        !input.files[0]
    ) {
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {

        preview.src =
            event.target.result;

        preview.style.display =
            "block";

    };

    reader.readAsDataURL(
        input.files[0]
    );

}


// =========================================
// AUTO HIDE ADMIN ALERT
// =========================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const alerts =
            document.querySelectorAll(
                ".alert"
            );

        alerts.forEach(
            function (alert) {

                setTimeout(
                    function () {

                        alert.style.transition =
                            "opacity 0.5s";

                        alert.style.opacity =
                            "0";

                        setTimeout(
                            function () {

                                alert.remove();

                            },
                            500
                        );

                    },
                    5000
                );

            }
        );

    }
);


// =========================================
// SEARCH TABLE
// =========================================

function searchTable(
    inputId,
    tableId
) {

    const input =
        document.getElementById(inputId);

    const table =
        document.getElementById(tableId);

    if (!input || !table) {
        return;
    }

    const search =
        input.value.toLowerCase();

    const rows =
        table.querySelectorAll(
            "tbody tr"
        );

    rows.forEach(
        function (row) {

            const text =
                row.textContent.toLowerCase();

            if (
                text.includes(search)
            ) {

                row.style.display = "";

            } else {

                row.style.display =
                    "none";

            }

        }
    );

}


// =========================================
// SIDEBAR ACTIVE LINK
// =========================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const currentPage =
            window.location.pathname
                .split("/")
                .pop();

        const links =
            document.querySelectorAll(
                ".admin-nav a"
            );

        links.forEach(
            function (link) {

                const href =
                    link.getAttribute("href");

                if (
                    href === currentPage
                ) {

                    link.classList.add(
                        "active"
                    );

                }

            }
        );

    }
);