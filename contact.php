<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Contact Us | Cafelia</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">


<style>
/* =========================================================
   CAFELIA CONTACT PAGE
   Professional coffeehouse styling
========================================================= */

:root {
    --contact-espresso: #2f1b13;
    --contact-dark: #24140f;
    --contact-coffee: #4a2a1c;
    --contact-caramel: #bd895b;
    --contact-gold: #d1a16d;
    --contact-cream: #f7f1e8;
    --contact-soft: #efe4d6;
    --contact-white: #fffdf9;
    --contact-muted: #77685f;
    --contact-line: rgba(74,42,28,.13);
}

body {
    margin: 0;
    background: var(--contact-cream);
    color: var(--contact-espresso);
}

/* =========================================================
   NAVBAR — SAME CAFELIA STYLE
========================================================= */

.navbar {
    position: sticky !important;
    top: 0;
    z-index: 9999;
    width: 100%;
    background: rgba(36, 21, 15, .97) !important;
    border-bottom: 1px solid rgba(214, 173, 130, .18);
    box-shadow: 0 8px 30px rgba(20, 10, 5, .12);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}

.nav-container {
    width: 100%;
    max-width: 1380px;
    min-height: 82px;
    margin: 0 auto;
    padding: 0 42px;
    display: flex;
    align-items: center;
}

.logo {
    min-width: 145px;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
}

.logo-text {
    display: inline-block;
    font-family: "Playfair Display", Georgia, "Times New Roman", serif;
    font-size: 27px;
    font-weight: 700;
    letter-spacing: 4px;
    line-height: 1;
    color: #f8f1e8;
    white-space: nowrap;
}

.nav-menu {
    margin-left: auto;
    margin-right: 30px;
    display: flex;
    align-items: center;
    gap: 34px;
}

.nav-menu a {
    position: relative;
    color: rgba(255,255,255,.88);
    font-family: "DM Sans", Arial, sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.25px;
    line-height: 1;
    text-decoration: none;
    text-transform: uppercase;
}

.nav-menu a:hover,
.nav-menu a.active {
    color: #d8b892;
}

.nav-menu a.active::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: -8px;
    height: 2px;
    border-radius: 2px;
    background: #d8b892;
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-actions a {
    min-height: 38px;
    padding: 9px 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    text-decoration: none;
    text-transform: uppercase;
}

.nav-cart {
    border: 1px solid rgba(214,173,130,.55);
    color: #fff;
}




/* =========================================================
   ACCOUNT / PROFILE — EXACT HOMEPAGE COMPONENT
   ========================================================= */

.account-menu {
    position: relative;
}

.account-trigger {
    appearance: none;
    -webkit-appearance: none;
    position: relative;
    min-height: 46px;
    padding: 5px 13px 5px 6px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(255, 255, 255, .16);
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(255,255,255,.105), rgba(255,255,255,.035));
    color: #fffaf3;
    font-family: inherit;
    cursor: pointer;
    backdrop-filter: blur(20px) saturate(140%);
    -webkit-backdrop-filter: blur(20px) saturate(140%);
    box-shadow:
        0 8px 26px rgba(0,0,0,.16),
        inset 0 1px 0 rgba(255,255,255,.12);
    transition: background .25s ease, border-color .25s ease,
                box-shadow .25s ease, transform .25s ease;
}

.account-trigger::before {
    content: "";
    position: absolute;
    inset: 1px;
    border-radius: inherit;
    background: linear-gradient(120deg, rgba(255,255,255,.08), transparent 38%, transparent 72%, rgba(255,255,255,.025));
    pointer-events: none;
}

.account-trigger:hover,
.account-trigger[aria-expanded="true"] {
    background: linear-gradient(135deg, rgba(255,255,255,.15), rgba(216,163,109,.07));
    border-color: rgba(216,163,109,.42);
    box-shadow:
        0 12px 32px rgba(0,0,0,.21),
        0 0 0 4px rgba(216,163,109,.045),
        inset 0 1px 0 rgba(255,255,255,.16);
    transform: translateY(-1px);
}

.profile-avatar,
.dropdown-avatar {
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 50%;
    color: #fffaf3;
    background: linear-gradient(145deg, #c99968 0%, #9c623a 100%);
    border: 1px solid rgba(255,255,255,.30);
    box-shadow:
        0 4px 12px rgba(0,0,0,.20),
        inset 0 1px 0 rgba(255,255,255,.25);
    font-family: "Playfair Display", Georgia, serif;
    font-weight: 700;
}

.profile-avatar {
    width: 36px;
    height: 36px;
    font-size: 14px;
}

.profile-name {
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: rgba(255,250,243,.94);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .025em;
}

.profile-chevron {
    width: 20px;
    height: 20px;
    display: grid;
    place-items: center;
    color: rgba(255,250,243,.56);
    font-size: 14px;
    line-height: 1;
    transition: transform .22s ease, color .22s ease;
}

.account-trigger[aria-expanded="true"] .profile-chevron {
    color: #d8a36d;
    transform: rotate(180deg);
}

.account-dropdown {
    position: absolute;
    top: calc(100% + 14px);
    right: 0;
    width: 305px;
    padding: 10px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.19);
    border-radius: 20px;
    background: linear-gradient(145deg, rgba(61,37,26,.82), rgba(29,18,13,.93));
    backdrop-filter: blur(26px) saturate(145%);
    -webkit-backdrop-filter: blur(26px) saturate(145%);
    box-shadow:
        0 26px 65px rgba(0,0,0,.34),
        0 8px 25px rgba(0,0,0,.14),
        inset 0 1px 0 rgba(255,255,255,.12);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px) scale(.975);
    transform-origin: top right;
    pointer-events: none;
    transition: opacity .22s ease, visibility .22s ease,
                transform .22s cubic-bezier(.2,.8,.2,1);
}

.account-dropdown::after {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 100% 0%, rgba(216,163,109,.11), transparent 34%),
        linear-gradient(135deg, rgba(255,255,255,.035), transparent 40%);
    pointer-events: none;
}

.account-dropdown::before {
    content: "";
    position: absolute;
    top: -7px;
    right: 28px;
    width: 14px;
    height: 14px;
    border-left: 1px solid rgba(255,255,255,.18);
    border-top: 1px solid rgba(255,255,255,.18);
    background: rgba(55,33,23,.92);
    transform: rotate(45deg);
}

.account-menu.open .account-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

.dropdown-profile {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 10px 14px;
}

.dropdown-avatar {
    width: 44px;
    height: 44px;
    font-size: 17px;
}

.dropdown-profile div {
    min-width: 0;
}

.dropdown-profile strong {
    display: block;
    color: #fffaf3;
    font-size: 13px;
    line-height: 1.3;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-profile small {
    display: block;
    margin-top: 4px;
    color: rgba(255,250,243,.52);
    font-size: 10px;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-divider {
    position: relative;
    z-index: 1;
    height: 1px;
    margin: 3px 5px 7px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.12), transparent);
}

.dropdown-item {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px;
    margin: 2px 0;
    border: 1px solid transparent;
    border-radius: 13px;
    color: #fffaf3;
    text-decoration: none;
    transition: background .18s ease, border-color .18s ease, transform .18s ease;
}

.dropdown-item:hover {
    background: rgba(255,255,255,.075);
    border-color: rgba(255,255,255,.06);
    transform: translateX(2px);
}

.dropdown-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    border: 1px solid rgba(216,163,109,.20);
    border-radius: 10px;
    background: rgba(216,163,109,.085);
    color: #e0b17d;
    font-size: 14px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
}

.dropdown-item strong {
    display: block;
    color: rgba(255,250,243,.94);
    font-size: 11px;
    font-weight: 700;
}

.dropdown-item small {
    display: block;
    margin-top: 3px;
    color: rgba(255,250,243,.45);
    font-size: 9px;
    line-height: 1.4;
}

.dropdown-logout {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    border-radius: 11px;
    color: rgba(255,225,217,.80);
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    transition: background .18s ease, color .18s ease;
}

.dropdown-logout:hover {
    background: rgba(169,71,55,.13);
    color: #ffd8cf;
}

.nav-login {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 20px;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(185,130,82,.96), rgba(145,88,51,.96));
    color: #fffaf3;
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .14em;
    box-shadow:
        0 8px 22px rgba(0,0,0,.15),
        inset 0 1px 0 rgba(255,255,255,.18);
    transition: all .22s ease;
}

.nav-login:hover {
    transform: translateY(-1px);
    box-shadow:
        0 11px 27px rgba(0,0,0,.20),
        inset 0 1px 0 rgba(255,255,255,.20);
}


/* Keep the profile button from being affected by generic nav link rules. */
.nav-actions .account-trigger,
.nav-actions .profile-login {
    text-transform: none;
}




/* =========================================================
   PAGE HEADER
========================================================= */

.page-header {
    position: relative;
    min-height: 390px !important;
    padding: 90px 7% 80px !important;
    display: flex;
    align-items: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 80% 35%, rgba(193,139,91,.20), transparent 30%),
        linear-gradient(120deg, #f8f1e8, #eee2d4) !important;
}

.page-header::before {
    content: "CONTACT";
    position: absolute;
    right: -25px;
    bottom: -65px;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(130px, 18vw, 260px);
    font-weight: 700;
    line-height: .8;
    color: rgba(74,42,28,.045);
    pointer-events: none;
}

.page-header > div {
    position: relative;
    z-index: 2;
    width: min(900px, 100%);
    margin: 0 auto;
    text-align: center;
}

.page-header p {
    margin: 0 0 18px !important;
    color: var(--contact-caramel) !important;
    font-size: .68rem !important;
    font-weight: 900 !important;
    letter-spacing: .18em !important;
    text-transform: uppercase;
}

.page-header p::before {
    content: "";
    display: inline-block;
    width: 32px;
    height: 1px;
    margin: 0 12px 3px 0;
    background: var(--contact-caramel);
}

.page-header h1 {
    margin: 0 0 17px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: clamp(3.4rem, 7vw, 6.5rem) !important;
    line-height: .92 !important;
    letter-spacing: -.05em !important;
    color: var(--contact-espresso) !important;
}

.page-header span {
    color: var(--contact-muted) !important;
    font-size: .98rem !important;
    line-height: 1.7;
}

/* =========================================================
   CONTACT AREA
========================================================= */

.contact-section {
    width: min(1240px, 86%);
    margin: 0 auto;
    padding: 105px 0;
    display: grid;
    grid-template-columns: .86fr 1.14fr;
    gap: 75px;
    align-items: start;
}

.contact-info {
    padding-top: 15px;
}

.contact-info .section-label {
    margin: 0 0 16px !important;
    color: var(--contact-caramel) !important;
    font-size: .66rem !important;
    font-weight: 900 !important;
    letter-spacing: .18em !important;
    text-transform: uppercase;
}

.contact-info h2 {
    margin: 0 0 20px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: clamp(2.7rem, 4.5vw, 4.6rem) !important;
    line-height: .98 !important;
    letter-spacing: -.04em !important;
    color: var(--contact-espresso) !important;
}

.contact-info > p:not(.section-label) {
    max-width: 500px;
    margin: 0 0 42px !important;
    color: var(--contact-muted) !important;
    font-size: .93rem !important;
    line-height: 1.85 !important;
}

.contact-item {
    display: flex;
    gap: 17px;
    padding: 20px 0;
    border-top: 1px solid var(--contact-line);
}

.contact-item:last-child {
    border-bottom: 1px solid var(--contact-line);
}

.contact-icon {
    width: 43px !important;
    height: 43px !important;
    flex: 0 0 43px;
    display: grid !important;
    place-items: center;
    border-radius: 13px;
    background: #eadaca !important;
    color: var(--contact-coffee);
    font-size: 1rem;
}

.contact-item h3 {
    margin: 1px 0 7px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: 1.18rem !important;
    color: var(--contact-espresso) !important;
}

.contact-item p {
    margin: 2px 0 !important;
    color: var(--contact-muted) !important;
    font-size: .8rem !important;
    line-height: 1.55 !important;
}

/* =========================================================
   FORM
========================================================= */

.contact-form-container {
    position: relative;
    padding: 42px;
    border: 1px solid var(--contact-line);
    border-radius: 22px;
    background: rgba(255,253,249,.78);
    box-shadow: 0 22px 55px rgba(54,32,21,.07);
}

.contact-form-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 42px;
    width: 70px;
    height: 3px;
    border-radius: 0 0 5px 5px;
    background: var(--contact-caramel);
}

.contact-form-container h2 {
    margin: 0 0 30px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: 2.2rem !important;
    letter-spacing: -.03em;
    color: var(--contact-espresso) !important;
}

.contact-form {
    display: grid;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    color: var(--contact-coffee) !important;
    font-size: .66rem !important;
    font-weight: 900 !important;
    letter-spacing: .09em;
    text-transform: uppercase;
}

.form-group input,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid rgba(74,42,28,.14) !important;
    border-radius: 11px !important;
    outline: none;
    background: #fffdf9 !important;
    color: var(--contact-espresso) !important;
    padding: 14px 15px !important;
    font-family: inherit;
    font-size: .84rem !important;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.form-group textarea {
    min-height: 145px;
    resize: vertical;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #aa9a8e !important;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: rgba(189,137,91,.75) !important;
    background: #fff !important;
    box-shadow: 0 0 0 4px rgba(189,137,91,.10);
}

.contact-form .btn-primary {
    width: 100%;
    min-height: 52px;
    margin-top: 4px;
    border: 0 !important;
    border-radius: 999px !important;
    background: var(--contact-espresso) !important;
    color: #fffaf3 !important;
    font-size: .69rem !important;
    font-weight: 900 !important;
    letter-spacing: .11em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
}

.contact-form .btn-primary:hover {
    transform: translateY(-2px);
    background: #42251a !important;
    box-shadow: 0 13px 28px rgba(47,27,19,.18);
}

/* =========================================================
   FAQ
========================================================= */

.faq-section {
    padding: 110px 7%;
    background: #f0e6da !important;
}

.faq-section .section-heading {
    max-width: 720px;
    margin: 0 auto 48px;
    text-align: center;
}

.faq-section .section-heading p {
    margin: 0 0 14px !important;
    color: var(--contact-caramel) !important;
    font-size: .66rem !important;
    font-weight: 900 !important;
    letter-spacing: .17em !important;
    text-transform: uppercase;
}

.faq-section .section-heading h2 {
    margin: 0 !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: clamp(2.6rem, 5vw, 4.5rem) !important;
    line-height: 1 !important;
    letter-spacing: -.04em !important;
    color: var(--contact-espresso) !important;
}

.faq-grid {
    width: min(1150px, 100%);
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.faq-card {
    padding: 27px 29px;
    border: 1px solid rgba(74,42,28,.11);
    border-radius: 16px;
    background: rgba(255,253,249,.75);
    transition: transform .25s ease, box-shadow .25s ease;
}

.faq-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 30px rgba(53,31,20,.07);
}

.faq-card h3 {
    margin: 0 0 11px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: 1.15rem !important;
    color: var(--contact-espresso) !important;
}

.faq-card p {
    margin: 0 !important;
    color: var(--contact-muted) !important;
    font-size: .82rem !important;
    line-height: 1.7 !important;
}

/* =========================================================
   CTA
========================================================= */

.cta-section {
    position: relative;
    min-height: 390px;
    padding: 75px 7%;
    display: grid;
    place-items: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 50% 0, rgba(208,161,109,.22), transparent 36%),
        linear-gradient(135deg, #2f1b13, #1e100c) !important;
}

.cta-section::before {
    content: "CAFELIA";
    position: absolute;
    left: 50%;
    bottom: -85px;
    transform: translateX(-50%);
    white-space: nowrap;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(120px, 18vw, 250px);
    line-height: .8;
    color: rgba(255,255,255,.035);
}

.cta-section > div {
    position: relative;
    z-index: 2;
    max-width: 720px;
    text-align: center;
}

.cta-section h2 {
    margin: 0 0 15px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: clamp(2.9rem, 6vw, 5.3rem) !important;
    line-height: .95 !important;
    letter-spacing: -.045em !important;
    color: #fffaf3 !important;
}

.cta-section p {
    margin: 0 auto 28px !important;
    color: rgba(255,250,243,.63) !important;
    font-size: .9rem !important;
    line-height: 1.8 !important;
}

.cta-section .btn-primary {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    padding: 0 23px !important;
    border-radius: 999px !important;
    background: #d1a16d !important;
    color: #24140f !important;
    text-decoration: none !important;
    font-size: .68rem !important;
    font-weight: 900 !important;
    letter-spacing: .1em;
    text-transform: uppercase;
}

/* =========================================================
   FOOTER
========================================================= */

.footer {
    padding: 65px 7% 0 !important;
    background: #1b0f0b !important;
    color: #fffaf3;
}

.footer .footer-content {
    width: min(1250px, 100%);
    margin: 0 auto;
    padding-bottom: 55px;
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr;
    gap: 65px;
}

.footer h3 {
    margin: 0 0 15px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: 1.8rem !important;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #fffaf3 !important;
}

.footer h4 {
    margin: 4px 0 17px !important;
    color: #d1a16d !important;
    font-size: .65rem !important;
    letter-spacing: .15em;
    text-transform: uppercase;
}

.footer p {
    max-width: 320px;
    margin: 0 !important;
    color: rgba(255,250,243,.58) !important;
    font-size: .8rem !important;
    line-height: 1.75 !important;
}

.footer a {
    display: block;
    width: fit-content;
    margin-bottom: 10px;
    color: rgba(255,250,243,.58) !important;
    font-size: .78rem !important;
    text-decoration: none !important;
    transition: color .2s ease;
}

.footer a:hover {
    color: #d1a16d !important;
}

.footer .footer-bottom {
    border-top: 1px solid rgba(255,255,255,.08);
}

.footer .footer-bottom p {
    width: min(1250px, 100%);
    min-height: 70px;
    margin: 0 auto !important;
    display: flex;
    align-items: center;
    color: rgba(255,250,243,.4) !important;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1050px) {
    .navbar .nav-container {
        padding: 0 25px;
    }

    .navbar .nav-menu {
        gap: 20px;
        margin-right: 18px;
    }

    .contact-section {
        width: 90%;
        gap: 45px;
    }
}

@media (max-width: 820px) {
    .navbar {
        position: relative !important;
    }

    .navbar .nav-container {
        min-height: auto;
        padding: 18px 5%;
        flex-wrap: wrap;
        gap: 15px;
    }

    .navbar .nav-menu {
        order: 3;
        width: 100%;
        margin: 0;
        justify-content: center;
        flex-wrap: wrap;
        gap: 18px;
    }

    .navbar .nav-menu a {
        padding: 8px 0 !important;
    }

    .navbar .nav-menu a.active::after {
        bottom: 0;
    }

    .contact-section {
        grid-template-columns: 1fr;
        padding: 80px 0;
    }

    .contact-info {
        max-width: 700px;
    }

    .faq-grid {
        grid-template-columns: 1fr;
    }

    .footer .footer-content {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 560px) {
    .navbar .logo-text {
        font-size: 22px;
        letter-spacing: 3px;
    }

    .navbar .nav-menu {
        gap: 12px;
    }

    .navbar .nav-menu a {
        font-size: 9px !important;
        letter-spacing: 1px;
    }

    .navbar .nav-actions > a {
        min-height: 34px;
        padding: 8px 12px !important;
        font-size: 9px !important;
    }

    .page-header {
        min-height: 300px !important;
        padding: 65px 5% !important;
    }

    .page-header h1 {
        font-size: 3.3rem !important;
    }

    .contact-section {
        width: 90%;
    }

    .contact-form-container {
        padding: 30px 22px;
    }

    .contact-form-container::before {
        left: 22px;
    }

    .faq-section {
        padding: 80px 5%;
    }

    .footer .footer-content {
        grid-template-columns: 1fr;
        gap: 35px;
    }
}
</style>

</head>

<body>


<!-- ==========================================
     NAVBAR
========================================== -->

<header class="navbar">
    <div class="nav-container">

        <a href="index.php" class="logo" aria-label="Cafelia Home">
            <span class="logo-text">CAFELIA</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php" >HOME</a>
            <a href="menu.php">MENU</a>
            <a href="about.php">ABOUT US</a>
            <a href="join_team.php">JOIN OUR TEAM</a>
            <a href="contact.php" class="active">CONTACT</a>
        </nav>

        <div class="nav-actions">

            <!-- Glassmorphism Account Menu -->
            <div class="account-menu">

                <?php if (isset($_SESSION["user_id"])): ?>

                    <button type="button"
                            class="account-trigger"
                            aria-label="Open account menu"
                            aria-expanded="false"
                            aria-haspopup="true"
                            onclick="toggleAccountMenu(this)">
                        <span class="profile-avatar" aria-hidden="true">
                            <?php
                                $nav_name = trim($_SESSION["user_name"] ?? "User");
                                echo htmlspecialchars(strtoupper(substr($nav_name, 0, 1)));
                            ?>
                        </span>

                        <span class="profile-name">
                            <?php echo htmlspecialchars($_SESSION["user_name"] ?? "Profile"); ?>
                        </span>
                    </button>

                    <div class="account-dropdown">

                        <div class="dropdown-profile">
                            <span class="dropdown-avatar">
                                <?php
                                    echo htmlspecialchars(strtoupper(substr($nav_name, 0, 1)));
                                ?>
                            </span>

                            <div>
                                <strong><?php echo htmlspecialchars($nav_name); ?></strong>
                                <small>
                                    <?php echo htmlspecialchars($_SESSION["user_email"] ?? "Cafelia Member"); ?>
                                </small>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="cart.php" class="dropdown-item">
                            <span class="dropdown-icon">🛒</span>
                            <span>
                                <strong>Cart</strong>
                                <small>View your selected items</small>
                            </span>
                        </a>

                        <a href="join_team.php" class="dropdown-item">
                            <span class="dropdown-icon">✦</span>
                            <span>
                                <strong>Join Our Team</strong>
                                <small>Explore opportunities at Cafelia</small>
                            </span>
                        </a>

                        <a href="profile.php" class="dropdown-item">
                            <span class="dropdown-icon">♙</span>
                            <span>
                                <strong>Profile</strong>
                                <small>Manage your Cafelia account</small>
                            </span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <?php if (($_SESSION["user_role"] ?? "") === "admin"): ?>
                            <a href="admin/dashboard.php" class="dropdown-item">
                                <span class="dropdown-icon">⌘</span>
                                <span>
                                    <strong>Admin Dashboard</strong>
                                    <small>Manage Cafelia</small>
                                </span>
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>

                        <a href="logout.php" class="dropdown-logout">
                            <span>↪</span>
                            Log Out
                        </a>

                    </div>

                <?php else: ?>

                    <a href="login.php" class="nav-login">
                        LOGIN
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </div>
</header>


<!-- ==========================================
     PAGE HEADER
========================================== -->

<section class="page-header">

    <div>

        <p>
            WE'D LOVE TO HEAR FROM YOU
        </p>

        <h1>
            Contact Cafelia
        </h1>

        <span>
            Have a question? Send us a message.
        </span>

    </div>

</section>


<!-- ==========================================
     CONTACT SECTION
========================================== -->

<section class="contact-section">


    <!-- ======================================
         CONTACT INFORMATION
    ======================================= -->

    <div class="contact-info">

        <p class="section-label">
            GET IN TOUCH
        </p>

        <h2>
            Let's Talk Over Coffee
        </h2>

        <p>

            Whether you have a question about our menu,
            orders, or anything else, we're happy to help.

        </p>


        <!-- LOCATION -->

        <div class="contact-item">

            <div class="contact-icon">
                📍
            </div>

            <div>

                <h3>
                    Location
                </h3>

                <p>
                    Cafelia Coffee Shop
                </p>

                <p>
                    Dumaguete City, Negros Oriental
                </p>

            </div>

        </div>


        <!-- PHONE -->

        <div class="contact-item">

            <div class="contact-icon">
                📞
            </div>

            <div>

                <h3>
                    Phone
                </h3>

                <p>
                    +63 900 000 0000
                </p>

            </div>

        </div>


        <!-- EMAIL -->

        <div class="contact-item">

            <div class="contact-icon">
                ✉️
            </div>

            <div>

                <h3>
                    Email
                </h3>

                <p>
                    hello@cafelia.com
                </p>

            </div>

        </div>


        <!-- BUSINESS HOURS -->

        <div class="contact-item">

            <div class="contact-icon">
                🕐
            </div>

            <div>

                <h3>
                    Opening Hours
                </h3>

                <p>
                    Monday – Sunday
                </p>

                <p>
                    8:00 AM – 9:00 PM
                </p>

            </div>

        </div>

    </div>


    <!-- ======================================
         CONTACT FORM
    ======================================= -->

    <div class="contact-form-container">

        <h2>
            Send Us a Message
        </h2>


        <form
            action="#"
            method="POST"
            class="contact-form"
        >


            <!-- NAME -->

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your name"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <!-- SUBJECT -->

            <div class="form-group">

                <label for="subject">
                    Subject
                </label>

                <input
                    type="text"
                    id="subject"
                    name="subject"
                    placeholder="What is your message about?"
                    required
                >

            </div>


            <!-- MESSAGE -->

            <div class="form-group">

                <label for="message">
                    Message
                </label>

                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    placeholder="Write your message here..."
                    required
                ></textarea>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="btn-primary"
            >
                Send Message
            </button>

        </form>

    </div>

</section>


<!-- ==========================================
     FAQ SECTION
========================================== -->

<section class="faq-section">

    <div class="section-heading">

        <p>
            FAQ
        </p>

        <h2>
            Frequently Asked Questions
        </h2>

    </div>


    <div class="faq-grid">


        <div class="faq-card">

            <h3>
                Do I need an account to order?
            </h3>

            <p>

                Yes. You need to create a Cafelia
                account before placing an order.

            </p>

        </div>


        <div class="faq-card">

            <h3>
                Can I view my previous orders?
            </h3>

            <p>

                Yes. After logging in, you can view
                your orders from the My Orders page.

            </p>

        </div>


        <div class="faq-card">

            <h3>
                How can I contact Cafelia?
            </h3>

            <p>

                You can send us a message using the
                contact form or reach us through email.

            </p>

        </div>


        <div class="faq-card">

            <h3>
                Where can I see the menu?
            </h3>

            <p>

                Visit our Menu page to view our
                available coffee, treats, and food.

            </p>

        </div>

    </div>

</section>


<!-- ==========================================
     CTA
========================================== -->

<section class="cta-section">

    <div>

        <h2>
            Ready for some coffee?
        </h2>

        <p>
            Check out our menu and place your order.
        </p>

        <a
            href="menu.php"
            class="btn-primary"
        >
            View Menu
        </a>

    </div>

</section>


<!-- ==========================================
     FOOTER
========================================== -->

<footer class="footer">

    <div class="footer-content">


        <div>

            <h3>
                Cafelia
            </h3>

            <p>
                Coffee, treats, and moments
                worth remembering.
            </p>

        </div>


        <div>

            <h4>
                Quick Links
            </h4>

            <a href="index.php">
                Home
            </a>

            <a href="menu.php">
                Menu
            </a>

            <a href="about.php">
                About
            </a>

            <a href="contact.php">
                Contact
            </a>

        </div>


        <div>

            <h4>
                Customer
            </h4>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

            <a href="cart.php">
                Cart
            </a>

        </div>

    </div>


    <div class="footer-bottom">

        <p>

            © <?php echo date("Y"); ?>
            Cafelia.
            All Rights Reserved.

        </p>

    </div>

</footer>



<script>
function toggleAccountMenu(button) {
    const menu = button.closest(".account-menu");

    document.querySelectorAll(".account-menu.open").forEach(function (item) {
        if (item !== menu) {
            item.classList.remove("open");

            const trigger = item.querySelector(".account-trigger");
            if (trigger) {
                trigger.setAttribute("aria-expanded", "false");
            }
        }
    });

    const isOpen = menu.classList.toggle("open");
    button.setAttribute("aria-expanded", isOpen ? "true" : "false");
}

document.addEventListener("click", function (event) {
    document.querySelectorAll(".account-menu.open").forEach(function (menu) {
        if (!menu.contains(event.target)) {
            menu.classList.remove("open");

            const trigger = menu.querySelector(".account-trigger");
            if (trigger) {
                trigger.setAttribute("aria-expanded", "false");
            }
        }
    });
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".account-menu.open").forEach(function (menu) {
            menu.classList.remove("open");

            const trigger = menu.querySelector(".account-trigger");
            if (trigger) {
                trigger.setAttribute("aria-expanded", "false");
            }
        });
    }
});
</script>

<script src="js/script.js"></script>

</body>

</html>