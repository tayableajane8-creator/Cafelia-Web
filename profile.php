<?php
session_start();
require_once "config/database.php";
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }
$user_id=(int)$_SESSION["user_id"];
$success=""; $error="";
$stmt=$conn->prepare("SELECT id,name,email,role,created_at FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i",$user_id); $stmt->execute(); $result=$stmt->get_result(); $user=$result->fetch_assoc(); $stmt->close();
if(!$user){session_unset();session_destroy();header("Location: login.php");exit();}
if($_SERVER["REQUEST_METHOD"]==="POST"){
 $name=trim($_POST["name"]??""); $email=trim($_POST["email"]??""); $new_password=$_POST["new_password"]??""; $confirm=$_POST["confirm_password"]??"";
 if($name===""||$email===""){$error="Please complete your name and email address.";}
 elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){$error="Please enter a valid email address.";}
 elseif($new_password!==""&&strlen($new_password)<8){$error="Your new password must be at least 8 characters.";}
 elseif($new_password!==""&&$new_password!==$confirm){$error="The new passwords do not match.";}
 else{
  $check=$conn->prepare("SELECT id FROM users WHERE email=? AND id!=? LIMIT 1");$check->bind_param("si",$email,$user_id);$check->execute();$cr=$check->get_result();
  if($cr->num_rows>0){$error="That email address is already being used.";}else{
   if($new_password!==""){$hash=password_hash($new_password,PASSWORD_DEFAULT);$update=$conn->prepare("UPDATE users SET name=?,email=?,password=? WHERE id=?");$update->bind_param("sssi",$name,$email,$hash,$user_id);}
   else{$update=$conn->prepare("UPDATE users SET name=?,email=? WHERE id=?");$update->bind_param("ssi",$name,$email,$user_id);}
   if($update->execute()){$_SESSION["user_name"]=$name;$_SESSION["user_email"]=$email;$user["name"]=$name;$user["email"]=$email;$success="Your profile has been updated successfully.";}else{$error="Unable to update your profile. Please try again.";}$update->close();
  }$check->close();
 }
}
$initial=strtoupper(substr(trim($user["name"]),0,1));$member_since=!empty($user["created_at"])?date("F Y",strtotime($user["created_at"])):"";
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Profile | Cafelia</title><link rel="stylesheet" href="css/style.css"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet"><style>
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

/* NAVBAR — SAME CAFELIA STYLE */

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

/* ACCOUNT / PROFILE — EXACT HOMEPAGE COMPONENT */

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

/* SHARED */

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

/* HERO */

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

/* HERO VISUAL */

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

/* STATS */

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

/* CULTURE */

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

/* POSITIONS */

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

/* APPLICATION */

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

/* CTA */

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

/* FOOTER */

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

/* RESPONSIVE */

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

.profile-page{background:#f7f1e8;min-height:100vh;color:#2f1b13;font-family:"DM Sans",Arial,sans-serif}
.profile-hero{position:relative;overflow:hidden;padding:78px 7% 72px;background:radial-gradient(circle at 82% 35%,rgba(193,139,91,.20),transparent 31%),linear-gradient(120deg,#f8f1e8,#eee2d4)}
.profile-hero::after{content:"PROFILE";position:absolute;right:-20px;bottom:-65px;font-family:"Playfair Display",Georgia,serif;font-size:clamp(130px,18vw,250px);font-weight:700;line-height:.8;color:rgba(74,42,28,.045);pointer-events:none}
.profile-hero-inner{position:relative;z-index:2;width:min(1120px,100%);margin:auto}.profile-kicker{margin:0 0 16px;color:#bd895b;font-size:.68rem;font-weight:900;letter-spacing:.18em;text-transform:uppercase}.profile-kicker::before{content:"";display:inline-block;width:34px;height:1px;margin:0 12px 3px 0;background:#bd895b}.profile-hero h1{margin:0 0 16px;font-family:"Playfair Display",Georgia,serif;font-size:clamp(3.6rem,7vw,6.5rem);line-height:.9;letter-spacing:-.055em}.profile-hero h1 span{color:#bd895b}.profile-hero p{max-width:610px;margin:0;color:#77685f;font-size:.95rem;line-height:1.8}
.profile-section{width:min(1120px,88%);margin:auto;padding:90px 0 110px;display:grid;grid-template-columns:320px 1fr;gap:42px;align-items:start}.profile-card,.profile-form-card{border:1px solid rgba(74,42,28,.12);border-radius:22px;background:rgba(255,253,249,.86);box-shadow:0 22px 55px rgba(54,32,21,.07)}.profile-card{position:sticky;top:105px;padding:32px;text-align:center}.profile-avatar-large{width:94px;height:94px;margin:0 auto 20px;display:grid;place-items:center;border-radius:50%;background:linear-gradient(145deg,#d1a16d,#9c623a);border:2px solid rgba(255,255,255,.75);box-shadow:0 12px 28px rgba(74,42,28,.16),inset 0 1px 0 rgba(255,255,255,.35);color:#fffaf3;font-family:"Playfair Display",Georgia,serif;font-size:35px;font-weight:700}.profile-card h2{margin:0 0 7px;font-family:"Playfair Display",Georgia,serif;font-size:1.55rem;line-height:1.1}.profile-email{margin:0 0 22px;color:#77685f;font-size:.76rem;line-height:1.5;overflow-wrap:anywhere}.profile-meta{padding-top:18px;border-top:1px solid rgba(74,42,28,.13);text-align:left}.profile-meta-row{display:flex;justify-content:space-between;gap:14px;padding:10px 0}.profile-meta-row span:first-child{color:#77685f;font-size:.65rem;letter-spacing:.08em;text-transform:uppercase}.profile-meta-row span:last-child{color:#4a2a1c;font-size:.72rem;font-weight:700;text-align:right}
.profile-form-card{padding:40px}.form-kicker{margin:0 0 10px;color:#bd895b;font-size:.65rem;font-weight:900;letter-spacing:.17em;text-transform:uppercase}.profile-form-card h2{margin:0 0 9px;font-family:"Playfair Display",Georgia,serif;font-size:2.45rem;line-height:1;letter-spacing:-.035em}.form-intro{display:block;margin-bottom:30px;color:#77685f;font-size:.8rem;line-height:1.65}.alert{margin-bottom:22px;padding:13px 15px;border-radius:11px;font-size:.76rem;line-height:1.5}.alert-success{border:1px solid rgba(93,121,75,.22);background:rgba(93,121,75,.08);color:#536c43}.alert-error{border:1px solid rgba(155,76,58,.20);background:rgba(155,76,58,.07);color:#8a493b}.profile-form{display:grid;gap:20px}.form-group{display:flex;flex-direction:column;gap:8px}.form-group label{color:#4a2a1c;font-size:.65rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase}.form-group input{width:100%;min-height:49px;padding:13px 15px;border:1px solid rgba(74,42,28,.14);border-radius:11px;outline:none;background:#fffdf9;color:#2f1b13;font-family:"DM Sans",Arial,sans-serif;font-size:.84rem;transition:.2s}.form-group input:focus{border-color:rgba(189,137,91,.75);background:#fff;box-shadow:0 0 0 4px rgba(189,137,91,.10)}.form-group input::placeholder{color:#aa9a8e}.password-block{margin-top:8px;padding-top:27px;border-top:1px solid rgba(74,42,28,.13)}.password-block h3{margin:0 0 7px;font-family:"Playfair Display",Georgia,serif;font-size:1.45rem}.password-block p{margin:0;color:#77685f;font-size:.75rem;line-height:1.6}.password-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.save-btn{min-height:52px;margin-top:4px;border:0;border-radius:999px;background:#2f1b13;color:#fffaf3;font-family:"DM Sans",Arial,sans-serif;font-size:.68rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase;cursor:pointer;transition:.25s}.save-btn:hover{transform:translateY(-2px);background:#42251a;box-shadow:0 13px 28px rgba(47,27,19,.18)}
.profile-footer{padding:55px 7% 30px;background:#1b0f0b;color:#fffaf3}.profile-footer-inner{width:min(1120px,100%);margin:auto;display:flex;justify-content:space-between;align-items:center;gap:25px}.profile-footer-brand{font-family:"Playfair Display",Georgia,serif;font-size:1.55rem;letter-spacing:.08em}.profile-footer p{margin:0;color:rgba(255,250,243,.42);font-size:.72rem}.profile-footer a{color:#d1a16d;font-size:.72rem;font-weight:700;text-decoration:none}
@media(max-width:850px){.profile-section{grid-template-columns:1fr;padding:70px 0 85px}.profile-card{position:relative;top:auto}.profile-footer-inner{flex-direction:column;align-items:flex-start}}@media(max-width:620px){.profile-section{width:90%}.profile-form-card{padding:28px 21px}.password-grid{grid-template-columns:1fr}.profile-hero{padding:60px 5%}.profile-hero h1{font-size:3.3rem}.profile-footer{padding-left:5%;padding-right:5%}}
</style></head><body><div class="profile-page"><header class="navbar">
    <div class="nav-container">

        <a href="index.php" class="logo" aria-label="Cafelia Home">
            <span class="logo-text">CAFELIA</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php" >HOME</a>
            <a href="menu.php">MENU</a>
            <a href="about.php">ABOUT US</a>
            <a href="join_team.php" class="active">JOIN OUR TEAM</a>
            <a href="contact.php">CONTACT</a>
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
<section class="profile-hero"><div class="profile-hero-inner"><p class="profile-kicker">YOUR CAFELIA ACCOUNT</p><h1>My <span>Profile.</span></h1><p>Keep your account details updated and make every Cafelia visit feel a little more personal.</p></div></section>
<section class="profile-section"><aside class="profile-card"><div class="profile-avatar-large"><?php echo htmlspecialchars($initial); ?></div><h2><?php echo htmlspecialchars($user["name"]); ?></h2><p class="profile-email"><?php echo htmlspecialchars($user["email"]); ?></p><div class="profile-meta"><div class="profile-meta-row"><span>Account</span><span><?php echo htmlspecialchars(ucfirst($user["role"]??"customer")); ?></span></div><?php if($member_since!==""): ?><div class="profile-meta-row"><span>Member Since</span><span><?php echo htmlspecialchars($member_since); ?></span></div><?php endif; ?></div></aside>
<div class="profile-form-card"><p class="form-kicker">ACCOUNT DETAILS</p><h2>Personal Information</h2><span class="form-intro">Update your name or email address below. Leave the password fields blank if you do not want to change your password.</span><?php if($success!==""): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?><?php if($error!==""): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?><form method="POST" class="profile-form" autocomplete="off"><div class="form-group"><label for="name">Full Name</label><input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user["name"]); ?>" placeholder="Enter your full name" autocomplete="name" required></div><div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user["email"]); ?>" placeholder="Enter your email address" autocomplete="email" required></div><div class="password-block"><h3>Change Password</h3><p>Only fill these fields if you want to set a new password.</p></div><div class="password-grid"><div class="form-group"><label for="new_password">New Password</label><input type="password" id="new_password" name="new_password" placeholder="At least 8 characters" minlength="8" autocomplete="new-password"></div><div class="form-group"><label for="confirm_password">Confirm Password</label><input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" minlength="8" autocomplete="new-password"></div></div><button type="submit" class="save-btn">SAVE CHANGES</button></form></div></section>
<footer class="profile-footer"><div class="profile-footer-inner"><div><div class="profile-footer-brand">CAFELIA</div><p>Coffee, treats, and moments worth remembering.</p></div><p>© <?php echo date("Y"); ?> Cafelia. All Rights Reserved.</p><a href="index.php">BACK TO HOME</a></div></footer></div>
<script>function toggleAccountMenu(button){const menu=button.closest(".account-menu");document.querySelectorAll(".account-menu.open").forEach(function(item){if(item!==menu){item.classList.remove("open");const trigger=item.querySelector(".account-trigger");if(trigger)trigger.setAttribute("aria-expanded","false");}});const isOpen=menu.classList.toggle("open");button.setAttribute("aria-expanded",isOpen?"true":"false");}document.addEventListener("click",function(event){document.querySelectorAll(".account-menu.open").forEach(function(menu){if(!menu.contains(event.target)){menu.classList.remove("open");const trigger=menu.querySelector(".account-trigger");if(trigger)trigger.setAttribute("aria-expanded","false");}});});document.addEventListener("keydown",function(event){if(event.key==="Escape"){document.querySelectorAll(".account-menu.open").forEach(function(menu){menu.classList.remove("open");const trigger=menu.querySelector(".account-trigger");if(trigger)trigger.setAttribute("aria-expanded","false");});}});</script></body></html>