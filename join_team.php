<?php
session_start();

$form_submitted = false;
$form_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $position === "") {
        $form_error = "Please complete your name, email, and preferred position.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = "Please enter a valid email address.";
    } else {
        /*
         * Front-end application form:
         * This currently confirms the submission on the page.
         * You can later connect this section to a database or email service.
         */
        $form_submitted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Join Our Team | Cafelia</title>

    <link rel="stylesheet" href="css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<style>
/* =========================================================
   CAFELIA — JOIN OUR TEAM
   Professional coffeehouse page
========================================================= */

:root {
    --team-espresso: #2f1b13;
    --team-dark: #21120d;
    --team-coffee: #4b2b1d;
    --team-caramel: #bd895b;
    --team-gold: #d3a16d;
    --team-cream: #f7f1e8;
    --team-soft: #efe3d5;
    --team-white: #fffdf9;
    --team-muted: #78685f;
    --team-line: rgba(74, 42, 28, .13);
}

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    background: var(--team-cream);
    color: var(--team-espresso);
    font-family: "DM Sans", Arial, sans-serif;
}

/* =========================================================
   CAFELIA SHARED TYPOGRAPHY
   Keep Join Our Team consistent with Home / Menu / About / Cart
   ========================================================= */

html,
body,
button,
input,
select,
textarea {
    font-family: "DM Sans", Arial, sans-serif;
}

.team-hero h1,
.section-heading h2,
.positions-header h2,
.position-main h3,
.application-intro h2,
.success-message h3,
.team-cta h2,
.footer-logo,
.logo-text {
    font-family: "Playfair Display", Georgia, "Times New Roman", serif;
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
   SHARED
========================================================= */

.eyebrow,
.section-label {
    color: var(--team-caramel);
    font-size: .67rem;
    font-weight: 900;
    letter-spacing: .18em;
    text-transform: uppercase;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.eyebrow::before,
.section-label::before {
    content: "";
    width: 34px;
    height: 1px;
    background: var(--team-caramel);
}

.primary-button,
.secondary-button,
.cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 17px;
    min-height: 51px;
    padding: 0 22px;
    border-radius: 999px;
    text-decoration: none;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .11em;
    text-transform: uppercase;
    transition: .25s ease;
}

.primary-button {
    background: var(--team-espresso);
    color: #fffaf3;
    box-shadow: 0 13px 28px rgba(47,27,19,.16);
}

.primary-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 17px 35px rgba(47,27,19,.22);
}

.secondary-button {
    border: 1px solid rgba(74,42,28,.17);
    background: rgba(255,255,255,.48);
    color: var(--team-espresso);
}

.secondary-button:hover {
    background: #fff;
    transform: translateY(-3px);
}

/* =========================================================
   HERO
========================================================= */

.team-hero {
    position: relative;
    min-height: 720px;
    padding: 95px 7%;
    display: grid;
    grid-template-columns: 1fr .9fr;
    gap: 60px;
    align-items: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 82% 45%, rgba(196,143,94,.22), transparent 32%),
        linear-gradient(120deg, #f8f2e9, #eee2d4);
}

.team-hero::after {
    content: "TEAM";
    position: absolute;
    right: -35px;
    bottom: -75px;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(150px, 22vw, 320px);
    font-weight: 700;
    line-height: .8;
    color: rgba(74,42,28,.045);
    pointer-events: none;
}

.team-hero-content {
    position: relative;
    z-index: 2;
    max-width: 660px;
}

.team-hero h1 {
    margin: 20px 0 24px;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(4rem, 7vw, 7rem);
    line-height: .88;
    letter-spacing: -.055em;
}

.team-hero h1 em {
    color: var(--team-caramel);
}

.team-hero-content > p {
    max-width: 570px;
    margin: 0 0 34px;
    color: var(--team-muted);
    font-size: 1rem;
    line-height: 1.85;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

/* =========================================================
   HERO VISUAL
========================================================= */

.team-hero-visual {
    min-height: 540px;
    position: relative;
    display: grid;
    place-items: center;
}

.hero-orbit {
    position: absolute;
    border: 1px solid rgba(74,42,28,.13);
    border-radius: 50%;
}

.hero-orbit-one {
    width: 510px;
    height: 510px;
}

.hero-orbit-two {
    width: 400px;
    height: 400px;
}

.hero-coffee-card {
    position: relative;
    z-index: 3;
    width: min(430px, 82%);
    min-height: 500px;
    padding: 24px;
    border: 7px solid rgba(255,255,255,.65);
    border-radius: 230px 230px 25px 25px;
    background:
        linear-gradient(160deg, rgba(72,40,25,.96), rgba(39,21,14,.98));
    box-shadow: 0 30px 65px rgba(48,28,18,.22);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
}

.coffee-card-top,
.coffee-card-bottom {
    position: relative;
    z-index: 4;
    display: flex;
    justify-content: space-between;
    color: rgba(255,250,243,.64);
    font-size: .57rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.coffee-card-bottom {
    display: block;
    padding: 17px;
    border: 1px solid rgba(255,255,255,.11);
    border-radius: 13px;
    background: rgba(255,255,255,.07);
    backdrop-filter: blur(10px);
}

.coffee-card-bottom strong,
.coffee-card-bottom small {
    display: block;
}

.coffee-card-bottom strong {
    color: #fffaf3;
    font-size: .75rem;
    letter-spacing: .04em;
}

.coffee-card-bottom small {
    margin-top: 5px;
    color: rgba(255,250,243,.56);
    font-size: .58rem;
    letter-spacing: .03em;
    text-transform: none;
}

.coffee-cup {
    position: relative;
    height: 290px;
    display: grid;
    place-items: center;
}

.cup {
    position: relative;
    width: 210px;
    height: 150px;
    border-radius: 0 0 95px 95px;
    background: linear-gradient(150deg, #fffaf2, #dfcbb6);
    box-shadow:
        inset -15px -13px 22px rgba(73,43,27,.13),
        0 25px 35px rgba(0,0,0,.25);
    display: grid;
    place-items: center;
}

.cup::before {
    content: "";
    position: absolute;
    top: -23px;
    left: 10px;
    width: 190px;
    height: 46px;
    border-radius: 50%;
    background: #fffaf3;
    box-shadow: inset 0 -10px 13px rgba(49,28,18,.13);
}

.cup::after {
    content: "";
    position: absolute;
    right: -48px;
    top: 18px;
    width: 75px;
    height: 72px;
    border: 14px solid #ead9c5;
    border-left: 0;
    border-radius: 0 55px 55px 0;
}

.cup span {
    position: relative;
    z-index: 2;
    margin-top: 15px;
    color: #6d4935;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 2px;
}

.cup-steam {
    position: absolute;
    width: 16px;
    height: 105px;
    border-left: 2px solid rgba(255,250,243,.28);
    border-radius: 50%;
    filter: blur(.2px);
    animation: steam 3.8s ease-in-out infinite;
}

.steam-one {
    left: 40%;
}

.steam-two {
    left: 50%;
    height: 125px;
    animation-delay: .7s;
}

.steam-three {
    left: 60%;
    height: 95px;
    animation-delay: 1.3s;
}

@keyframes steam {
    0%, 100% {
        transform: translateY(12px) scaleX(1);
        opacity: .2;
    }
    50% {
        transform: translateY(-8px) scaleX(1.3);
        opacity: .5;
    }
}

/* =========================================================
   STATS
========================================================= */

.team-stats {
    min-height: 125px;
    padding: 25px 7%;
    display: grid;
    grid-template-columns: 1fr 1px 1fr 1px 1fr;
    align-items: center;
    gap: 30px;
    background: var(--team-espresso);
    color: #fffaf3;
}

.team-stat {
    text-align: center;
}

.team-stat strong,
.team-stat span {
    display: block;
}

.team-stat strong {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.8rem;
}

.team-stat span {
    margin-top: 3px;
    color: rgba(255,250,243,.56);
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.team-stat-divider {
    width: 1px;
    height: 45px;
    background: rgba(255,255,255,.15);
}

/* =========================================================
   CULTURE
========================================================= */

.culture-section {
    padding: 120px 7%;
    background: var(--team-cream);
}

.section-heading {
    max-width: 760px;
    margin: 0 auto 55px;
    text-align: center;
}

.section-heading .section-label {
    display: inline-flex;
    justify-content: center;
    gap: 0;
}

.section-heading .section-label::before {
    display: none;
}

.section-heading h2 {
    margin: 17px 0 21px;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(3rem, 5vw, 5rem);
    line-height: .98;
    letter-spacing: -.045em;
}

.section-heading h2 em,
.positions-header h2 em,
.application-intro h2 em,
.team-cta h2 em {
    color: var(--team-caramel);
}

.section-heading > p {
    max-width: 620px;
    margin: 0 auto;
    color: var(--team-muted);
    line-height: 1.8;
    font-size: .9rem;
}

.culture-grid {
    width: min(1200px, 100%);
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.culture-card {
    position: relative;
    min-height: 355px;
    padding: 30px;
    border: 1px solid var(--team-line);
    border-radius: 18px;
    background: rgba(255,253,249,.78);
    transition: transform .3s ease, box-shadow .3s ease;
}

.culture-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 22px 42px rgba(62,38,25,.09);
}

.culture-card.featured {
    background: var(--team-espresso);
    border-color: transparent;
    color: #fffaf3;
}

.card-number {
    position: absolute;
    top: 25px;
    right: 27px;
    color: rgba(74,42,28,.28);
    font-size: .62rem;
    font-weight: 900;
    letter-spacing: .1em;
}

.featured .card-number {
    color: rgba(255,250,243,.32);
}

.culture-icon {
    width: 52px;
    height: 52px;
    margin-top: 28px;
    display: grid;
    place-items: center;
    border-radius: 15px;
    background: #eadaca;
    color: var(--team-coffee);
    font-size: 1.15rem;
}

.featured .culture-icon {
    background: rgba(255,255,255,.10);
    color: #d5a575;
}

.culture-card h3 {
    margin: 30px 0 13px;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.8rem;
}

.featured h3 {
    color: #fffaf3;
}

.culture-card p {
    margin: 0;
    color: var(--team-muted);
    line-height: 1.75;
    font-size: .83rem;
}

.featured p {
    color: rgba(255,250,243,.64);
}

.card-arrow {
    position: absolute;
    left: 30px;
    bottom: 25px;
    color: var(--team-caramel);
    font-size: 1.1rem;
}

/* =========================================================
   POSITIONS
========================================================= */

.positions-section {
    padding: 120px 7%;
    background: #f0e6da;
}

.positions-header {
    width: min(1200px, 100%);
    margin: 0 auto 50px;
    display: grid;
    grid-template-columns: 1fr .6fr;
    gap: 60px;
    align-items: end;
}

.positions-header h2 {
    margin: 16px 0 0;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(3rem, 5vw, 5rem);
    line-height: .96;
    letter-spacing: -.045em;
}

.positions-header > p {
    margin: 0;
    color: var(--team-muted);
    font-size: .88rem;
    line-height: 1.8;
}

.positions-list {
    width: min(1200px, 100%);
    margin: 0 auto;
}

.position-row {
    min-height: 125px;
    padding: 25px 0;
    display: grid;
    grid-template-columns: 55px 1.05fr 1fr 110px;
    gap: 25px;
    align-items: center;
    border-top: 1px solid var(--team-line);
}

.position-row:last-child {
    border-bottom: 1px solid var(--team-line);
}

.position-number {
    color: var(--team-caramel);
    font-size: .67rem;
    font-weight: 900;
}

.position-main h3 {
    margin: 0 0 7px;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.7rem;
}

.position-main span {
    color: var(--team-caramel);
    font-size: .57rem;
    font-weight: 900;
    letter-spacing: .1em;
}

.position-row > p {
    margin: 0;
    color: var(--team-muted);
    font-size: .78rem;
    line-height: 1.65;
}

.position-apply {
    justify-self: end;
    color: var(--team-espresso);
    font-size: .65rem;
    font-weight: 900;
    letter-spacing: .1em;
    text-decoration: none;
}

.position-apply:hover {
    color: var(--team-caramel);
}

/* =========================================================
   APPLICATION
========================================================= */

.application-section {
    width: min(1240px, 86%);
    margin: 0 auto;
    padding: 125px 0;
    display: grid;
    grid-template-columns: .75fr 1.25fr;
    gap: 80px;
    align-items: start;
}

.application-intro {
    padding-top: 15px;
}

.application-intro h2 {
    margin: 17px 0 20px;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(3rem, 5vw, 5rem);
    line-height: .96;
    letter-spacing: -.045em;
}

.application-intro > p {
    max-width: 460px;
    margin: 0;
    color: var(--team-muted);
    font-size: .88rem;
    line-height: 1.8;
}

.application-note {
    max-width: 380px;
    margin-top: 30px;
    padding: 17px;
    display: flex;
    gap: 13px;
    border: 1px solid var(--team-line);
    border-radius: 13px;
    background: #f0e5d8;
}

.application-note > span {
    color: var(--team-caramel);
}

.application-note strong,
.application-note small {
    display: block;
}

.application-note strong {
    font-family: "Playfair Display", Georgia, serif;
    font-size: .98rem;
}

.application-note small {
    margin-top: 4px;
    color: var(--team-muted);
    font-size: .68rem;
}

.application-card {
    padding: 42px;
    border: 1px solid var(--team-line);
    border-radius: 22px;
    background: rgba(255,253,249,.84);
    box-shadow: 0 24px 60px rgba(54,32,21,.07);
}

.team-form {
    display: grid;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-field label {
    color: var(--team-coffee);
    font-size: .62rem;
    font-weight: 900;
    letter-spacing: .1em;
}

.form-field input,
.form-field select,
.form-field textarea {
    width: 100%;
    border: 1px solid rgba(74,42,28,.14);
    border-radius: 11px;
    outline: none;
    padding: 14px 15px;
    background: #fffdf9;
    color: var(--team-espresso);
    font: inherit;
    font-size: .82rem;
    transition: .2s ease;
}

.form-field textarea {
    min-height: 145px;
    resize: vertical;
}

.form-field input::placeholder,
.form-field textarea::placeholder {
    color: #aa9a8e;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    border-color: rgba(189,137,91,.75);
    box-shadow: 0 0 0 4px rgba(189,137,91,.10);
}

.submit-button {
    min-height: 52px;
    border: 0;
    border-radius: 999px;
    background: var(--team-espresso);
    color: #fffaf3;
    font: inherit;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .11em;
    cursor: pointer;
    text-transform: uppercase;
    transition: .25s ease;
}

.submit-button:hover {
    transform: translateY(-2px);
    background: #42251a;
    box-shadow: 0 13px 28px rgba(47,27,19,.18);
}

.submit-button span {
    margin-left: 15px;
}

.form-disclaimer {
    margin: 0;
    text-align: center;
    color: #a39489;
    font-size: .62rem;
    line-height: 1.5;
}

.form-error {
    margin-bottom: 20px;
    padding: 13px 15px;
    border: 1px solid rgba(150,66,45,.18);
    border-radius: 10px;
    background: rgba(150,66,45,.07);
    color: #814536;
    font-size: .75rem;
}

.success-message {
    min-height: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.success-icon {
    width: 58px;
    height: 58px;
    margin-bottom: 20px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: var(--team-espresso);
    color: #d3a16d;
    font-size: 1.4rem;
}

.success-message .section-label::before {
    display: none;
}

.success-message h3 {
    margin: 14px 0;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 2.5rem;
}

.success-message p {
    max-width: 490px;
    margin: 0 auto 25px;
    color: var(--team-muted);
    font-size: .85rem;
    line-height: 1.75;
}

/* =========================================================
   CTA
========================================================= */

.team-cta {
    position: relative;
    min-height: 440px;
    padding: 80px 7%;
    display: grid;
    place-items: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 50% 0, rgba(208,161,109,.22), transparent 36%),
        linear-gradient(135deg, #2f1b13, #1e100c);
}

.team-cta::before {
    content: "CAFELIA";
    position: absolute;
    left: 50%;
    bottom: -85px;
    transform: translateX(-50%);
    white-space: nowrap;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(120px, 18vw, 250px);
    line-height: .8;
    color: rgba(255,255,255,.035);
}

.team-cta-content {
    position: relative;
    z-index: 2;
    max-width: 720px;
    text-align: center;
}

.team-cta-content > span {
    color: #d0a16e;
    font-size: .65rem;
    font-weight: 900;
    letter-spacing: .18em;
}

.team-cta h2 {
    margin: 17px 0;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(3rem, 6vw, 5.6rem);
    line-height: .92;
    letter-spacing: -.045em;
    color: #fffaf3;
}

.team-cta p {
    max-width: 540px;
    margin: 0 auto 28px;
    color: rgba(255,250,243,.62);
    line-height: 1.8;
    font-size: .88rem;
}

.cta-button {
    background: #d1a16d;
    color: #24140f;
}

/* =========================================================
   FOOTER
========================================================= */

.team-footer {
    padding-top: 65px;
    background: #1b0f0b;
    color: #fffaf3;
}

.footer-container {
    width: min(1250px, 86%);
    margin: 0 auto;
    padding-bottom: 55px;
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 60px;
}

.footer-logo {
    display: inline-block;
    margin-bottom: 16px;
    color: #fffaf3;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-decoration: none;
}

.footer-brand p,
.footer-column p {
    max-width: 310px;
    margin: 0 0 14px;
    color: rgba(255,250,243,.58);
    font-size: .78rem;
    line-height: 1.75;
}

.footer-column h3 {
    margin: 4px 0 18px;
    color: #d1a16d;
    font-size: .65rem;
    letter-spacing: .14em;
}

.footer-column a {
    display: block;
    width: fit-content;
    margin-bottom: 10px;
    color: rgba(255,250,243,.58);
    font-size: .77rem;
    text-decoration: none;
}

.footer-column a:hover {
    color: #d1a16d;
}

.footer-hours {
    margin-top: 25px !important;
    color: rgba(255,250,243,.42) !important;
    font-size: .65rem !important;
    letter-spacing: .1em;
}

.footer-bottom {
    min-height: 70px;
    padding: 0 7%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid rgba(255,255,255,.08);
    color: rgba(255,250,243,.38);
    font-size: .65rem;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1050px) {
    .nav-container {
        padding: 0 25px;
    }

    .nav-menu {
        gap: 20px;
        margin-right: 18px;
    }

    .team-hero {
        grid-template-columns: 1fr .8fr;
        padding-left: 5%;
        padding-right: 5%;
    }

    .application-section {
        width: 90%;
        gap: 45px;
    }
}

@media (max-width: 850px) {
    .nav-actions {
        display: flex;
        margin-left: auto;
    }

    .account-dropdown {
        right: -5px;
        width: min(305px, calc(100vw - 28px));
    }

    .navbar {
        position: relative !important;
    }

    .nav-container {
        min-height: auto;
        padding: 18px 5%;
        flex-wrap: wrap;
        gap: 15px;
    }

    .nav-menu {
        order: 3;
        width: 100%;
        margin: 0;
        justify-content: center;
        flex-wrap: wrap;
        gap: 18px;
    }

    .nav-menu a {
        padding: 8px 0;
    }

    .nav-menu a.active::after {
        bottom: 0;
    }

    .team-hero {
        grid-template-columns: 1fr;
        min-height: auto;
        padding-top: 75px;
        padding-bottom: 75px;
        text-align: center;
    }

    .team-hero-content {
        margin: 0 auto;
    }

    .eyebrow {
        justify-content: center;
    }

    .eyebrow::before {
        display: none;
    }

    .team-hero-content > p {
        margin-left: auto;
        margin-right: auto;
    }

    .hero-actions {
        justify-content: center;
    }

    .team-hero-visual {
        min-height: 500px;
    }

    .culture-grid {
        grid-template-columns: 1fr;
    }

    .culture-card {
        min-height: 300px;
    }

    .positions-header {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .position-row {
        grid-template-columns: 45px 1fr 100px;
    }

    .position-row > p {
        grid-column: 2 / 3;
    }

    .position-apply {
        grid-column: 3;
        grid-row: 1 / span 2;
    }

    .application-section {
        grid-template-columns: 1fr;
        padding-top: 90px;
        padding-bottom: 90px;
    }
}

@media (max-width: 600px) {
    .logo-text {
        font-size: 22px;
        letter-spacing: 3px;
    }

    .nav-menu {
        gap: 12px;
    }

    .nav-menu a {
        font-size: 9px;
        letter-spacing: 1px;
    }

    .nav-actions a {
        min-height: 34px;
        padding: 8px 12px;
        font-size: 9px;
    }

    .team-hero {
        padding: 58px 5% 65px;
    }

    .team-hero h1 {
        font-size: 3.5rem;
    }

    .team-hero-visual {
        min-height: 410px;
    }

    .hero-coffee-card {
        width: 80%;
        min-height: 390px;
        border-radius: 190px 190px 22px 22px;
    }

    .hero-orbit-one {
        width: 350px;
        height: 350px;
    }

    .hero-orbit-two {
        width: 285px;
        height: 285px;
    }

    .coffee-cup {
        height: 215px;
    }

    .cup {
        transform: scale(.78);
    }

    .team-stats {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 30px 7%;
    }

    .team-stat-divider {
        width: 60px;
        height: 1px;
        margin: 0 auto;
    }

    .culture-section,
    .positions-section {
        padding-left: 5%;
        padding-right: 5%;
    }

    .culture-section {
        padding-top: 85px;
        padding-bottom: 85px;
    }

    .positions-section {
        padding-top: 85px;
        padding-bottom: 85px;
    }

    .section-heading h2,
    .positions-header h2,
    .application-intro h2 {
        font-size: 2.8rem;
    }

    .position-row {
        grid-template-columns: 35px 1fr;
        gap: 10px 15px;
        padding: 24px 0;
    }

    .position-row > p {
        grid-column: 2;
    }

    .position-apply {
        grid-column: 2;
        grid-row: auto;
        justify-self: start;
    }

    .application-section {
        width: 90%;
    }

    .application-card {
        padding: 30px 22px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .footer-container {
        width: 90%;
        grid-template-columns: 1fr;
        gap: 35px;
    }

    .footer-bottom {
        min-height: auto;
        padding: 18px 5%;
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
}



/* =========================================================
   FINAL NAVBAR TEXT OVERRIDE
   Dropdown/account text must NOT inherit uppercase styling
   ========================================================= */

.nav-actions .account-dropdown a,
.nav-actions .account-dropdown a:hover,
.nav-actions .account-dropdown a.logout-link {
    font-family: "DM Sans", Arial, sans-serif !important;
    font-size: .67rem !important;
    font-weight: 700 !important;
    letter-spacing: .04em !important;
    text-transform: none !important;
}

.nav-actions .account-name {
    font-family: "DM Sans", Arial, sans-serif !important;
    text-transform: none !important;
    letter-spacing: .03em !important;
}

.nav-actions .account-dropdown-header strong,
.nav-actions .account-dropdown-header small {
    font-family: "DM Sans", Arial, sans-serif !important;
    text-transform: none !important;
}



.account-dropdown .dropdown-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    margin-right: 1px;
    border: 1px solid rgba(216,163,109,.20);
    border-radius: 10px;
    background: rgba(216,163,109,.085);
    color: #e0b17d;
    font-size: 14px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
}

.account-dropdown .logout-link {
    margin-top: 5px;
    border-top: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 11px !important;
    color: rgba(255,225,217,.80) !important;
}

.account-dropdown .logout-link:hover {
    background: rgba(169,71,55,.13);
    color: #ffd8cf !important;
}

</style>
</head>

<body>

<?php
$active_page = "join_team";
include "navbar.php";
?>



<!-- =========================================================
     HERO
========================================================= -->
<section class="team-hero">

    <div class="team-hero-content">

        <span class="eyebrow">WORK WITH CAFELIA</span>

        <h1>
            Good Coffee.<br>
            <em>Great People.</em>
        </h1>

        <p>
            We are building a team that believes a coffee shop is more than
            a place to grab a drink. It is a place to create moments,
            welcome people, and make every visit feel special.
        </p>

        <div class="hero-actions">
            <a href="#application" class="primary-button">
                APPLY WITH US
                <span>→</span>
            </a>

            <a href="#culture" class="secondary-button">
                MEET THE CAFELIA CULTURE
            </a>
        </div>

    </div>

    <div class="team-hero-visual">

        <div class="hero-orbit hero-orbit-one"></div>
        <div class="hero-orbit hero-orbit-two"></div>

        <div class="hero-coffee-card">
            <div class="coffee-card-top">
                <span>CAFELIA</span>
                <span>EST. 2026</span>
            </div>

            <div class="coffee-cup">
                <div class="cup-steam steam-one"></div>
                <div class="cup-steam steam-two"></div>
                <div class="cup-steam steam-three"></div>

                <div class="cup">
                    <span>CAFELIA</span>
                </div>
            </div>

            <div class="coffee-card-bottom">
                <strong>Made With Care</strong>
                <small>Every cup. Every guest. Every day.</small>
            </div>
        </div>

    </div>

</section>


<!-- =========================================================
     QUICK STATS
========================================================= -->
<section class="team-stats">

    <div class="team-stat">
        <strong>01</strong>
        <span>One Team</span>
    </div>

    <div class="team-stat-divider"></div>

    <div class="team-stat">
        <strong>∞</strong>
        <span>Room to Grow</span>
    </div>

    <div class="team-stat-divider"></div>

    <div class="team-stat">
        <strong>100%</strong>
        <span>Cafelia Spirit</span>
    </div>

</section>


<!-- =========================================================
     WHY JOIN
========================================================= -->
<section class="culture-section" id="culture">

    <div class="section-heading">

        <span class="section-label">WHY CAFELIA</span>

        <h2>
            Bring your energy.<br>
            <em>We'll bring the coffee.</em>
        </h2>

        <p>
            At Cafelia, we value people who care about the little things:
            a warm welcome, a well-made drink, a clean space, and a team
            that looks out for one another.
        </p>

    </div>

    <div class="culture-grid">

        <article class="culture-card featured">
            <div class="card-number">01</div>
            <div class="culture-icon">☕</div>
            <h3>People First</h3>
            <p>
                We create a welcoming environment for our guests and for
                every person who works beside us.
            </p>
            <span class="card-arrow">↗</span>
        </article>

        <article class="culture-card">
            <div class="card-number">02</div>
            <div class="culture-icon">✦</div>
            <h3>Learn & Grow</h3>
            <p>
                Every shift is a chance to improve your skills, discover
                your strengths, and learn something new.
            </p>
            <span class="card-arrow">↗</span>
        </article>

        <article class="culture-card">
            <div class="card-number">03</div>
            <div class="culture-icon">♡</div>
            <h3>Work With Heart</h3>
            <p>
                We believe thoughtful service starts with people who enjoy
                what they do and care about the experience they create.
            </p>
            <span class="card-arrow">↗</span>
        </article>

    </div>

</section>


<!-- =========================================================
     OPEN POSITIONS
========================================================= -->
<section class="positions-section">

    <div class="positions-header">

        <div>
            <span class="section-label">OPPORTUNITIES</span>

            <h2>
                Find your place<br>
                <em>at Cafelia.</em>
            </h2>
        </div>

        <p>
            Whether you're starting your coffee journey or bringing
            experience with you, we'd love to hear what you can bring
            to the team.
        </p>

    </div>

    <div class="positions-list">

        <div class="position-row">
            <div class="position-number">01</div>

            <div class="position-main">
                <h3>Barista</h3>
                <span>COFFEE · SERVICE · HOSPITALITY</span>
            </div>

            <p>
                Prepare quality drinks while creating a friendly,
                memorable experience for every guest.
            </p>

            <a href="#application" class="position-apply">APPLY →</a>
        </div>

        <div class="position-row">
            <div class="position-number">02</div>

            <div class="position-main">
                <h3>Service Crew</h3>
                <span>GUEST EXPERIENCE · TEAMWORK</span>
            </div>

            <p>
                Help keep Cafelia welcoming, organized, and ready
                for every coffee moment.
            </p>

            <a href="#application" class="position-apply">APPLY →</a>
        </div>

        <div class="position-row">
            <div class="position-number">03</div>

            <div class="position-main">
                <h3>Shift Lead</h3>
                <span>LEADERSHIP · OPERATIONS · SERVICE</span>
            </div>

            <p>
                Support the team, guide daily operations, and help
                maintain the Cafelia standard.
            </p>

            <a href="#application" class="position-apply">APPLY →</a>
        </div>

    </div>

</section>


<!-- =========================================================
     APPLICATION
========================================================= -->
<section class="application-section" id="application">

    <div class="application-intro">

        <span class="section-label">LET'S TALK</span>

        <h2>
            Ready to make<br>
            <em>something good?</em>
        </h2>

        <p>
            Tell us a little about yourself and the role you're interested
            in. If there's a place for you at Cafelia, we'll be happy to
            connect with you.
        </p>

        <div class="application-note">
            <span>✦</span>
            <div>
                <strong>Keep it simple.</strong>
                <small>We want to know what makes you, you.</small>
            </div>
        </div>

    </div>

    <div class="application-card">

        <?php if ($form_submitted): ?>

            <div class="success-message">
                <div class="success-icon">✓</div>

                <span class="section-label">APPLICATION RECEIVED</span>

                <h3>Thanks, <?php echo htmlspecialchars($name); ?>.</h3>

                <p>
                    Your interest in joining Cafelia has been received.
                    Our team can review your details and contact you
                    through the email you provided.
                </p>

                <a href="join-team.php" class="primary-button">
                    SEND ANOTHER APPLICATION
                    <span>→</span>
                </a>
            </div>

        <?php else: ?>

            <?php if ($form_error !== ""): ?>
                <div class="form-error">
                    <?php echo htmlspecialchars($form_error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="#application" class="team-form">

                <div class="form-row">

                    <div class="form-field">
                        <label for="name">FULL NAME</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Your full name"
                            value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="email">EMAIL ADDRESS</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                            required
                        >
                    </div>

                </div>

                <div class="form-row">

                    <div class="form-field">
                        <label for="phone">PHONE NUMBER</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Your phone number"
                            value="<?php echo htmlspecialchars($_POST["phone"] ?? ""); ?>"
                        >
                    </div>

                    <div class="form-field">
                        <label for="position">PREFERRED POSITION</label>

                        <select id="position" name="position" required>
                            <option value="" disabled <?php echo empty($_POST["position"]) ? "selected" : ""; ?>>
                                Select a position
                            </option>

                            <option value="Barista" <?php echo ($_POST["position"] ?? "") === "Barista" ? "selected" : ""; ?>>
                                Barista
                            </option>

                            <option value="Service Crew" <?php echo ($_POST["position"] ?? "") === "Service Crew" ? "selected" : ""; ?>>
                                Service Crew
                            </option>

                            <option value="Shift Lead" <?php echo ($_POST["position"] ?? "") === "Shift Lead" ? "selected" : ""; ?>>
                                Shift Lead
                            </option>

                            <option value="Other" <?php echo ($_POST["position"] ?? "") === "Other" ? "selected" : ""; ?>>
                                Other / General Application
                            </option>
                        </select>
                    </div>

                </div>

                <div class="form-field">
                    <label for="message">TELL US ABOUT YOU</label>

                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        placeholder="Tell us about your experience, strengths, or why you'd like to join Cafelia."
                    ><?php echo htmlspecialchars($_POST["message"] ?? ""); ?></textarea>
                </div>

                <button type="submit" class="submit-button">
                    SUBMIT APPLICATION
                    <span>→</span>
                </button>

                <p class="form-disclaimer">
                    By submitting this form, you are expressing your interest
                    in joining the Cafelia team.
                </p>

            </form>

        <?php endif; ?>

    </div>

</section>


<!-- =========================================================
     CTA
========================================================= -->
<section class="team-cta">

    <div class="team-cta-content">

        <span>YOUR NEXT COFFEE CHAPTER</span>

        <h2>
            Maybe your<br>
            <em>seat is here.</em>
        </h2>

        <p>
            Great cafés are built by great people.
            Come be part of the Cafelia story.
        </p>

        <a href="#application" class="cta-button">
            JOIN CAFELIA
            <span>→</span>
        </a>

    </div>

</section>


<!-- =========================================================
     FOOTER
========================================================= -->
<footer class="team-footer">

    <div class="footer-container">

        <div class="footer-brand">

            <a href="index.php" class="footer-logo">
                CAFELIA
            </a>

            <p>
                Good coffee, meaningful moments, and a place
                worth coming back to.
            </p>

        </div>

        <div class="footer-column">

            <h3>EXPLORE</h3>

            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="about.php">About Us</a>
            <a href="join-team.php">Join Our Team</a>
            <a href="contact.php">Contact</a>

        </div>

        <div class="footer-column">

            <h3>VISIT</h3>

            <p>
                123 Coffee Street<br>
                Dumaguete City, Negros Oriental
            </p>

            <p class="footer-hours">
                MON — SUN<br>
                8:00 AM — 9:00 PM
            </p>

        </div>

    </div>

    <div class="footer-bottom">

        <div>
            © <?php echo date("Y"); ?> Cafelia. All rights reserved.
        </div>

        <div>
            Made for coffee moments.
        </div>

    </div>

</footer>




<script>
function toggleAccountMenu(button) {
    const menu = button.closest(".account-menu");
    const isOpen = menu.classList.toggle("open");
    button.setAttribute("aria-expanded", isOpen ? "true" : "false");
}

document.addEventListener("click", function (event) {
    document.querySelectorAll(".account-menu.open").forEach(function (menu) {
        if (!menu.contains(event.target)) {
            menu.classList.remove("open");

            const button = menu.querySelector(".account-trigger");
            if (button) {
                button.setAttribute("aria-expanded", "false");
            }
        }
    });
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".account-menu.open").forEach(function (menu) {
            menu.classList.remove("open");

            const button = menu.querySelector(".account-trigger");
            if (button) {
                button.setAttribute("aria-expanded", "false");
                button.focus();
            }
        });
    }
});
</script>
</body>
</html>
