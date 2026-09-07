/* =========================================================
   CAFELIA COFFEE SHOP
   GLOBAL JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       MOBILE NAVIGATION
    ===================================================== */

    const navContainer = document.querySelector(".nav-container");
    const navMenu = document.querySelector(".nav-menu");

    if (navContainer && navMenu) {

        const menuButton = document.createElement("button");

        menuButton.classList.add("mobile-menu-btn");
        menuButton.setAttribute("type", "button");
        menuButton.setAttribute("aria-label", "Toggle navigation");
        menuButton.setAttribute("aria-expanded", "false");

        menuButton.innerHTML = `
            <span></span>
            <span></span>
            <span></span>
        `;

        navContainer.insertBefore(menuButton, navMenu);

        menuButton.addEventListener("click", function () {

            navMenu.classList.toggle("show");

            const isOpen = navMenu.classList.contains("show");

            menuButton.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );

        });


        /* Close menu after clicking a link */

        const navLinks = navMenu.querySelectorAll("a");

        navLinks.forEach(function (link) {

            link.addEventListener("click", function () {

                navMenu.classList.remove("show");

                menuButton.setAttribute(
                    "aria-expanded",
                    "false"
                );

            });

        });

    }


    /* =====================================================
       HEADER SCROLL EFFECT
    ===================================================== */

    const navbar = document.querySelector(".navbar");

    if (navbar) {

        window.addEventListener("scroll", function () {

            if (window.scrollY > 30) {

                navbar.classList.add("scrolled");

            } else {

                navbar.classList.remove("scrolled");

            }

        });

    }


    /* =====================================================
       CATEGORY FILTER
    ===================================================== */

    const categoryButtons =
        document.querySelectorAll(".category-btn");

    const productCards =
        document.querySelectorAll(".product-card");


    if (categoryButtons.length > 0 && productCards.length > 0) {

        categoryButtons.forEach(function (button) {

            button.addEventListener("click", function () {

                /* Remove active state */

                categoryButtons.forEach(function (btn) {
                    btn.classList.remove("active");
                });

                /* Add active state */

                button.classList.add("active");

                const selectedCategory =
                    button.getAttribute("data-category");


                productCards.forEach(function (card) {

                    const productCategory =
                        card.getAttribute("data-category");


                    if (
                        selectedCategory === "all" ||
                        selectedCategory === productCategory
                    ) {

                        card.style.display = "";

                        setTimeout(function () {
                            card.classList.add("visible");
                        }, 10);

                    } else {

                        card.style.display = "none";
                        card.classList.remove("visible");

                    }

                });

            });

        });

    }


    /* =====================================================
       SMOOTH SCROLL
    ===================================================== */

    const scrollLinks =
        document.querySelectorAll('a[href^="#"]');


    scrollLinks.forEach(function (link) {

        link.addEventListener("click", function (event) {

            const targetId =
                link.getAttribute("href");

            if (
                targetId === "#" ||
                targetId.length === 0
            ) {
                return;
            }


            const target =
                document.querySelector(targetId);


            if (target) {

                event.preventDefault();

                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }

        });

    });


    /* =====================================================
       IMAGE ERROR HANDLING
    ===================================================== */

    const images =
        document.querySelectorAll("img");


    images.forEach(function (image) {

        image.addEventListener("error", function () {

            image.classList.add("image-error");

            /*
             * Prevent broken image icons from looking
             * confusing during development.
             */

            image.alt =
                image.alt || "Cafelia image";

        });

    });


    /* =====================================================
       QUANTITY CONTROLS
    ===================================================== */

    const quantityInputs =
        document.querySelectorAll(".quantity-input");


    quantityInputs.forEach(function (input) {

        input.addEventListener("change", function () {

            let value =
                parseInt(input.value);


            if (isNaN(value) || value < 1) {

                input.value = 1;

            }

        });

    });


    /* =====================================================
       ALERT AUTO HIDE
    ===================================================== */

    const alerts =
        document.querySelectorAll(".alert");


    alerts.forEach(function (alert) {

        setTimeout(function () {

            alert.style.opacity = "0";

            alert.style.transform = "translateY(-10px)";

            setTimeout(function () {

                alert.remove();

            }, 300);

        }, 5000);

    });


    /* =====================================================
       FORM VALIDATION
    ===================================================== */

    const forms =
        document.querySelectorAll("form");


    forms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            const requiredFields =
                form.querySelectorAll("[required]");


            let valid = true;


            requiredFields.forEach(function (field) {

                if (!field.value.trim()) {

                    valid = false;

                    field.classList.add("input-error");

                } else {

                    field.classList.remove("input-error");

                }

            });


            if (!valid) {

                event.preventDefault();

                const firstError =
                    form.querySelector(".input-error");


                if (firstError) {
                    firstError.focus();
                }

            }

        });

    });


    /* =====================================================
       REMOVE INPUT ERROR WHILE TYPING
    ===================================================== */

    const formInputs =
        document.querySelectorAll(
            ".form-control"
        );


    formInputs.forEach(function (input) {

        input.addEventListener("input", function () {

            if (input.value.trim()) {

                input.classList.remove(
                    "input-error"
                );

            }

        });

    });


    /* =====================================================
       BACK TO TOP BUTTON
    ===================================================== */

    const backToTop =
        document.querySelector(".back-to-top");


    if (backToTop) {

        window.addEventListener("scroll", function () {

            if (window.scrollY > 500) {

                backToTop.classList.add("show");

            } else {

                backToTop.classList.remove("show");

            }

        });


        backToTop.addEventListener("click", function () {

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });

        });

    }


    /* =====================================================
       CURRENT YEAR
    ===================================================== */

    const yearElements =
        document.querySelectorAll(".current-year");


    yearElements.forEach(function (element) {

        element.textContent =
            new Date().getFullYear();

    });

});


function filterProducts(category, event) {

    const products = document.querySelectorAll(".menu-product");
    const buttons = document.querySelectorAll(".category-btn");

    // Remove active from all buttons
    buttons.forEach(function(button) {
        button.classList.remove("active");
    });

    // Add active to clicked button
    event.currentTarget.classList.add("active");

    // Filter products
    products.forEach(function(product) {

        const productCategory = product.getAttribute("data-category");

        if (
            category === "all" ||
            productCategory === category
        ) {
            product.style.display = "";
        } else {
            product.style.display = "none";
        }

    });
}
