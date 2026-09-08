<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us | Cafelia</title>

    <link rel="stylesheet" href="css/style.css">


<style>
/* =========================================================
   CAFELIA ABOUT PAGE — PROFESSIONAL COFFEEHOUSE DESIGN
   Page-specific styles intentionally live here so this page
   can be copied directly as about.php.
========================================================= */

:root {
    --about-espresso: #2f1b13;
    --about-dark: #24140f;
    --about-coffee: #4a2a1c;
    --about-caramel: #b8875c;
    --about-gold: #cfa16e;
    --about-cream: #f7f1e8;
    --about-soft: #eee4d7;
    --about-white: #fffdf9;
    --about-muted: #78685e;
    --about-line: rgba(74, 42, 28, .14);
}

body {
    background: var(--about-cream);
    color: var(--about-espresso);
}

/* ---------- HERO ---------- */

.about-hero {
    position: relative;
    min-height: 690px;
    padding: 95px 7% 90px;
    display: grid;
    grid-template-columns: minmax(0, 1.02fr) minmax(420px, .98fr);
    gap: 70px;
    align-items: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 82% 48%, rgba(202,157,110,.22), transparent 31%),
        linear-gradient(120deg, #f8f2e9 0%, #f1e7da 100%);
}

.about-hero::before {
    content: "CAFELIA";
    position: absolute;
    right: -35px;
    bottom: -60px;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(110px, 17vw, 270px);
    font-weight: 700;
    line-height: .8;
    letter-spacing: -.04em;
    color: rgba(74,42,28,.045);
    pointer-events: none;
}

.about-hero-content {
    position: relative;
    z-index: 2;
    max-width: 650px;
}

.about-eyebrow,
.about-section-label {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    color: var(--about-caramel) !important;
    font-size: .68rem !important;
    font-weight: 900 !important;
    letter-spacing: .18em !important;
    text-transform: uppercase;
}

.about-eyebrow::before,
.about-section-label::before {
    content: "";
    width: 34px;
    height: 1px;
    background: var(--about-caramel);
}

.about-hero h1 {
    margin: 18px 0 22px !important;
    max-width: 620px;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: clamp(4rem, 7vw, 7.2rem) !important;
    line-height: .88 !important;
    letter-spacing: -.055em !important;
    color: var(--about-espresso) !important;
}

.about-hero h1 span {
    color: var(--about-caramel) !important;
    font-style: italic;
}

.about-hero-content > p {
    max-width: 560px;
    margin: 0 0 34px !important;
    color: var(--about-muted) !important;
    font-size: 1.04rem !important;
    line-height: 1.85 !important;
}

.about-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 13px;
}

.about-primary-btn,
.about-secondary-btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 15px;
    min-height: 50px;
    padding: 0 21px !important;
    border-radius: 999px;
    text-decoration: none !important;
    font-size: .69rem !important;
    font-weight: 900 !important;
    letter-spacing: .11em;
    text-transform: uppercase;
    transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
}

.about-primary-btn {
    background: var(--about-espresso) !important;
    color: #fffaf3 !important;
    box-shadow: 0 12px 28px rgba(47,27,19,.16);
}

.about-primary-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 32px rgba(47,27,19,.22);
}

.about-secondary-btn {
    background: rgba(255,255,255,.48) !important;
    border: 1px solid rgba(74,42,28,.18);
    color: var(--about-espresso) !important;
}

.about-secondary-btn:hover {
    transform: translateY(-3px);
    background: #fff !important;
}

/* ---------- HERO VISUAL ---------- */

.about-hero-visual {
    position: relative;
    min-height: 530px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-coffee-glow {
    position: absolute;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: rgba(190,137,91,.16);
    filter: blur(20px);
}

.hero-circle {
    position: absolute;
    border: 1px solid rgba(104,66,44,.14);
    border-radius: 50%;
}

.hero-circle-one {
    width: 490px;
    height: 490px;
}

.hero-circle-two {
    width: 400px;
    height: 400px;
}

.about-hero-image {
    position: relative;
    z-index: 3;
    width: min(520px, 88%);
    aspect-ratio: 4 / 5;
    overflow: hidden;
    border-radius: 250px 250px 24px 24px;
    box-shadow: 0 28px 60px rgba(48,28,18,.20);
    border: 7px solid rgba(255,255,255,.68);
}

.about-hero-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .7s ease;
}

.about-hero-image:hover img {
    transform: scale(1.035);
}

.about-floating-card {
    position: absolute;
    z-index: 5;
    left: 2%;
    bottom: 9%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 17px;
    border: 1px solid rgba(255,255,255,.38);
    border-radius: 14px;
    background: rgba(47,27,19,.68);
    color: #fffaf3;
    box-shadow: 0 14px 35px rgba(35,20,13,.18);
    backdrop-filter: blur(13px);
    -webkit-backdrop-filter: blur(13px);
}

.floating-icon {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 11px;
    background: rgba(255,255,255,.12);
}

.about-floating-card strong,
.about-floating-card small {
    display: block;
}

.about-floating-card strong {
    font-size: .75rem;
    letter-spacing: .03em;
}

.about-floating-card small {
    margin-top: 3px;
    color: rgba(255,250,243,.68);
    font-size: .62rem;
}

/* ---------- INTRO STRIP ---------- */

.about-intro-strip {
    min-height: 120px;
    padding: 24px 7%;
    display: grid;
    grid-template-columns: 1fr 1px 1fr 1px 1fr;
    align-items: center;
    gap: 35px;
    background: var(--about-espresso) !important;
    color: #fffaf3;
}

.about-intro-item {
    text-align: center;
}

.about-intro-item strong,
.about-intro-item span {
    display: block;
}

.about-intro-item strong {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 1.65rem;
    font-weight: 700;
}

.about-intro-item span {
    margin-top: 3px;
    color: rgba(255,250,243,.56);
    font-size: .64rem;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.about-intro-divider {
    width: 1px;
    height: 42px;
    background: rgba(255,255,255,.17);
}

/* ---------- STORY ---------- */

.about-story {
    padding: 125px 7%;
    display: grid;
    grid-template-columns: minmax(350px, .9fr) minmax(0, 1.1fr);
    gap: clamp(60px, 9vw, 130px);
    align-items: center;
    background: #fbf7f0;
}

.story-image {
    position: relative;
    max-width: 570px;
    justify-self: center;
}

.story-image-frame {
    position: relative;
    overflow: hidden;
    aspect-ratio: 4 / 4.8;
    border-radius: 18px;
    box-shadow: 0 25px 55px rgba(53,31,20,.16);
}

.story-image-frame::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 60%, rgba(35,20,13,.18));
    pointer-events: none;
}

.story-image-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.story-image-label {
    position: absolute;
    right: -28px;
    bottom: 32px;
    z-index: 3;
    min-width: 170px;
    padding: 15px 17px;
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 12px;
    background: rgba(47,27,19,.72);
    color: #fffaf3;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 15px 30px rgba(34,19,12,.17);
}

.story-image-label span,
.story-image-label small {
    display: block;
}

.story-image-label span {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 1.25rem;
    letter-spacing: .06em;
}

.story-image-label small {
    margin-top: 3px;
    color: rgba(255,250,243,.65);
    font-size: .6rem;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.story-content {
    max-width: 690px;
}

.story-content h2,
.about-heading h2,
.experience-header h2,
.about-cta h2 {
    margin: 16px 0 24px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    color: var(--about-espresso) !important;
    letter-spacing: -.035em !important;
}

.story-content h2 {
    font-size: clamp(2.8rem, 5vw, 5rem) !important;
    line-height: .98 !important;
}

.story-content h2 span,
.experience-header h2 span {
    color: var(--about-caramel) !important;
    font-style: italic;
}

.story-content > p {
    color: var(--about-muted) !important;
    font-size: .96rem !important;
    line-height: 1.9 !important;
    margin: 0 0 18px !important;
}

.story-highlight {
    margin-top: 30px;
    display: flex;
    gap: 17px;
    padding: 22px;
    border: 1px solid var(--about-line);
    border-radius: 15px;
    background: #f4eadf;
}

.story-quote {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 3.5rem;
    line-height: .7;
    color: var(--about-caramel);
}

.story-highlight p {
    margin: 0 !important;
    color: var(--about-coffee) !important;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 1.08rem !important;
    line-height: 1.5 !important;
}

.story-highlight span {
    display: inline-block;
    margin-top: 9px;
    color: var(--about-caramel);
    font-size: .62rem;
    font-weight: 900;
    letter-spacing: .11em;
    text-transform: uppercase;
}

/* ---------- BELIEFS ---------- */

.beliefs-section {
    padding: 120px 7%;
    background: var(--about-cream) !important;
}

.about-heading {
    max-width: 700px;
    margin: 0 auto 55px;
    text-align: center;
}

.about-heading > span {
    justify-content: center;
}

.about-heading > span::before {
    display: none;
}

.about-heading h2 {
    font-size: clamp(2.8rem, 5vw, 4.8rem) !important;
    line-height: 1 !important;
}

.about-heading h2 strong {
    color: var(--about-caramel) !important;
    font-style: italic;
}

.about-heading p {
    max-width: 600px;
    margin: 0 auto !important;
    color: var(--about-muted) !important;
    line-height: 1.8 !important;
}

.beliefs-grid {
    max-width: 1250px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.belief-card {
    min-height: 370px;
    padding: 30px;
    display: flex;
    flex-direction: column;
    border: 1px solid var(--about-line);
    border-radius: 18px;
    background: rgba(255,253,249,.78);
    box-shadow: 0 12px 30px rgba(64,39,26,.045);
    transition: transform .3s ease, box-shadow .3s ease;
}

.belief-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 22px 40px rgba(64,39,26,.10);
}

.featured-belief {
    background: var(--about-espresso) !important;
    color: #fffaf3;
    border-color: transparent;
}

.belief-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.belief-number {
    color: var(--about-caramel);
    font-size: .67rem;
    font-weight: 900;
    letter-spacing: .12em;
}

.belief-icon {
    width: 52px;
    height: 52px;
    display: grid;
    place-items: center;
    border-radius: 15px;
    background: #f1e5d8;
    color: var(--about-coffee);
    font-size: 1.25rem;
}

.featured-belief .belief-icon {
    background: rgba(255,255,255,.10);
    color: #d8ae7b;
}

.belief-card h3 {
    margin: 35px 0 14px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: 1.85rem !important;
    color: var(--about-espresso) !important;
}

.featured-belief h3 {
    color: #fffaf3 !important;
}

.belief-card p {
    margin: 0 !important;
    color: var(--about-muted) !important;
    line-height: 1.75 !important;
    font-size: .88rem !important;
}

.featured-belief p {
    color: rgba(255,250,243,.66) !important;
}

.belief-bottom {
    margin-top: auto;
    padding-top: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid var(--about-line);
    color: var(--about-caramel);
    font-size: .6rem;
    font-weight: 900;
    letter-spacing: .12em;
}

.featured-belief .belief-bottom {
    border-top-color: rgba(255,255,255,.12);
}

.belief-arrow {
    font-size: 1.05rem;
}

/* ---------- EXPERIENCE ---------- */

.experience-section {
    padding: 120px 7%;
    background: #f1e8dd;
}

.experience-header {
    max-width: 1250px;
    margin: 0 auto 50px;
    display: grid;
    grid-template-columns: 1fr .65fr;
    gap: 60px;
    align-items: end;
}

.experience-header h2 {
    max-width: 600px;
    margin-bottom: 0 !important;
    font-size: clamp(2.8rem, 5vw, 5rem) !important;
    line-height: .98 !important;
}

.experience-header > p {
    margin: 0 !important;
    color: var(--about-muted) !important;
    line-height: 1.8 !important;
    font-size: .9rem !important;
}

.experience-grid {
    max-width: 1250px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.experience-card {
    position: relative;
    min-height: 310px;
    padding: 26px;
    overflow: hidden;
    border: 1px solid rgba(74,42,28,.11);
    border-radius: 17px;
    background: rgba(255,253,249,.66);
    transition: transform .3s ease, background .3s ease;
}

.experience-card:hover {
    transform: translateY(-6px);
    background: #fffdf9;
}

.experience-number {
    position: absolute;
    top: 24px;
    right: 24px;
    color: rgba(74,42,28,.26);
    font-size: .62rem;
    font-weight: 900;
    letter-spacing: .1em;
}

.experience-icon {
    width: 50px;
    height: 50px;
    margin-top: 26px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: #eadaca;
    font-size: 1.15rem;
}

.experience-card h3 {
    margin: 28px 0 12px !important;
    font-family: Georgia, "Times New Roman", serif !important;
    font-size: 1.45rem !important;
    color: var(--about-espresso) !important;
}

.experience-card p {
    margin: 0 !important;
    color: var(--about-muted) !important;
    line-height: 1.7 !important;
    font-size: .82rem !important;
}

.experience-arrow {
    position: absolute;
    left: 26px;
    bottom: 23px;
    color: var(--about-caramel);
    font-size: 1.1rem;
}

/* ---------- CTA ---------- */

.about-cta {
    position: relative;
    min-height: 470px;
    padding: 80px 7%;
    display: grid;
    place-items: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 50% 0%, rgba(210,163,111,.22), transparent 34%),
        linear-gradient(135deg, #2f1b13, #1e100c) !important;
}

.about-cta::before,
.about-cta::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 50%;
}

.about-cta::before {
    width: 430px;
    height: 430px;
    top: -270px;
    left: -100px;
}

.about-cta::after {
    width: 520px;
    height: 520px;
    right: -230px;
    bottom: -330px;
}

.about-cta-content {
    position: relative;
    z-index: 2;
    max-width: 760px;
    text-align: center;
}

.about-cta-content > span {
    color: #d0a16e !important;
    font-size: .66rem !important;
    font-weight: 900 !important;
    letter-spacing: .18em !important;
}

.about-cta h2 {
    margin: 17px 0 18px !important;
    color: #fffaf3 !important;
    font-size: clamp(3rem, 6vw, 5.8rem) !important;
    line-height: .92 !important;
}

.about-cta h2 em {
    color: #d1a16d !important;
    font-style: italic;
}

.about-cta-content p {
    max-width: 580px;
    margin: 0 auto 29px !important;
    color: rgba(255,250,243,.64) !important;
    line-height: 1.8 !important;
}

.about-cta-button {
    display: inline-flex !important;
    align-items: center;
    gap: 18px;
    min-height: 51px;
    padding: 0 23px !important;
    border-radius: 999px;
    background: #d1a16d !important;
    color: #24140f !important;
    text-decoration: none !important;
    font-size: .68rem !important;
    font-weight: 900 !important;
    letter-spacing: .1em;
    text-transform: uppercase;
}

/* ---------- FOOTER ---------- */

.site-footer {
    background: #1b0f0b !important;
    color: #fffaf3;
    padding-top: 70px;
}

.site-footer .footer-container {
    width: min(1250px, 86%);
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1.35fr .8fr .8fr 1fr;
    gap: 55px;
    padding-bottom: 60px;
}

.site-footer .footer-logo {
    display: inline-block;
    color: #fffaf3 !important;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-decoration: none;
}

.site-footer .footer-brand > p {
    max-width: 300px;
    color: rgba(255,250,243,.62) !important;
    line-height: 1.7 !important;
}

.site-footer .footer-description {
    font-size: .78rem !important;
}

.footer-socials {
    display: flex;
    gap: 9px;
    margin-top: 20px;
}

.footer-socials a {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 50%;
    color: #fffaf3 !important;
    text-decoration: none;
}

.site-footer .footer-column h3 {
    margin: 0 0 19px !important;
    color: #d0a16e !important;
    font-size: .66rem !important;
    letter-spacing: .14em !important;
    text-transform: uppercase;
}

.site-footer .footer-column ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.site-footer .footer-column li {
    margin-bottom: 10px;
}

.site-footer .footer-column li a {
    color: rgba(255,250,243,.62) !important;
    text-decoration: none;
    font-size: .78rem;
    transition: color .2s ease;
}

.site-footer .footer-column li a:hover {
    color: #d0a16e !important;
}

.contact-item {
    display: flex;
    gap: 11px;
    margin-bottom: 14px;
}

.contact-icon {
    flex: 0 0 22px;
}

.contact-item p {
    margin: 0 !important;
    color: rgba(255,250,243,.62) !important;
    font-size: .78rem !important;
    line-height: 1.55 !important;
}

.site-footer .footer-bottom {
    border-top: 1px solid rgba(255,255,255,.08);
}

.site-footer .footer-bottom-container {
    width: min(1250px, 86%);
    min-height: 72px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.site-footer .footer-bottom p,
.site-footer .footer-legal {
    margin: 0 !important;
    color: rgba(255,250,243,.42) !important;
    font-size: .68rem !important;
}

.site-footer .footer-legal {
    display: flex;
    gap: 12px;
}

.site-footer .footer-legal a {
    color: inherit !important;
    text-decoration: none;
}

/* ---------- RESPONSIVE ---------- */

@media (max-width: 1050px) {
    .navbar {
        padding: 0 4%;
    }

    .navbar nav {
        gap: 17px;
    }

    .about-hero {
        padding-left: 5%;
        padding-right: 5%;
        gap: 35px;
    }

    .beliefs-grid {
        grid-template-columns: 1fr;
    }

    .belief-card {
        min-height: 300px;
    }

    .experience-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .site-footer .footer-container {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 820px) {
    .navbar {
        position: relative !important;
        min-height: auto;
        padding: 18px 5%;
    }

    .navbar,
    .navbar nav {
        flex-wrap: wrap;
    }

    .navbar nav {
        width: 100%;
        justify-content: center;
        gap: 16px;
    }

    .navbar nav > a {
        padding: 10px 0;
    }

    .navbar nav > a.active::after {
        bottom: 3px;
    }

    .about-nav-actions {
        margin-left: auto;
    }

    .about-hero {
        grid-template-columns: 1fr;
        padding-top: 70px;
        text-align: center;
    }

    .about-hero-content {
        margin: 0 auto;
    }

    .about-eyebrow {
        justify-content: center;
    }

    .about-eyebrow::before {
        display: none;
    }

    .about-hero h1 {
        font-size: clamp(3.5rem, 13vw, 6rem) !important;
    }

    .about-hero-content > p {
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .about-hero-actions {
        justify-content: center;
    }

    .about-hero-visual {
        min-height: 470px;
    }

    .about-story {
        grid-template-columns: 1fr;
        padding-top: 90px;
        padding-bottom: 90px;
    }

    .story-content {
        max-width: 100%;
    }

    .experience-header {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

@media (max-width: 600px) {
    .logo-text {
        font-size: 22px;
    }

    .navbar nav {
        gap: 11px;
    }

    .navbar nav > a {
        font-size: .61rem;
    }

    .about-nav-actions {
        width: 100%;
        justify-content: center;
        margin: 8px 0 0;
    }

    .about-hero {
        padding: 58px 5% 70px;
    }

    .about-hero h1 {
        font-size: 3.45rem !important;
    }

    .about-hero-visual {
        min-height: 390px;
    }

    .about-hero-image {
        width: 84%;
    }

    .hero-circle-one {
        width: 330px;
        height: 330px;
    }

    .hero-circle-two {
        width: 275px;
        height: 275px;
    }

    .about-floating-card {
        left: 0;
        bottom: 3%;
    }

    .about-intro-strip {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 30px 7%;
    }

    .about-intro-divider {
        width: 60px;
        height: 1px;
        margin: 0 auto;
    }

    .about-story,
    .beliefs-section,
    .experience-section {
        padding-left: 5%;
        padding-right: 5%;
    }

    .story-image-label {
        right: 12px;
    }

    .story-content h2,
    .about-heading h2,
    .experience-header h2 {
        font-size: 2.8rem !important;
    }

    .experience-grid {
        grid-template-columns: 1fr;
    }

    .site-footer .footer-container {
        grid-template-columns: 1fr;
        gap: 35px;
    }

    .site-footer .footer-bottom-container {
        width: 90%;
        flex-direction: column;
        justify-content: center;
        padding: 18px 0;
    }
}
</style>


<style>
/* =========================================================
   CAFELIA — PREMIUM GLASSMORPHISM ACCOUNT NAVIGATION
   ========================================================= */

.navbar {
    position: sticky;
    top: 0;
    z-index: 9999;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
}

.nav-container {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
}

.nav-menu {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-left: auto;
}

.nav-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-left: 22px;
    flex-shrink: 0;
}

.account-menu {
    position: relative;
}

.account-trigger {
    appearance: none;
    -webkit-appearance: none;
    position: relative;
    min-height: 48px;
    padding: 5px 12px 5px 6px;
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

.nav-cart {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 44px;
    padding: 0 17px;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 999px;
    background: rgba(255,255,255,.055);
    color: rgba(255,250,243,.92);
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .14em;
    box-shadow:
        0 8px 22px rgba(0,0,0,.12),
        inset 0 1px 0 rgba(255,255,255,.10);
    backdrop-filter: blur(14px) saturate(135%);
    -webkit-backdrop-filter: blur(14px) saturate(135%);
    transition: all .22s ease;
}

.nav-cart:hover {
    border-color: rgba(216,163,109,.42);
    background: rgba(216,163,109,.10);
    color: #fffaf3;
    transform: translateY(-1px);
    box-shadow:
        0 11px 27px rgba(0,0,0,.18),
        inset 0 1px 0 rgba(255,255,255,.14);
}

.cart-icon {
    font-size: 14px;
    line-height: 1;
    filter: saturate(.75);
}

.account-login-icon {
    min-width: 48px;
    padding: 5px 6px;
    text-decoration: none;
}

.account-login-icon .profile-avatar-guest {
    width: 36px;
    height: 36px;
    font-family: Arial, sans-serif;
    font-size: 19px;
    line-height: 1;
}

.account-login-icon:hover {
    text-decoration: none;
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

@media (max-width: 900px) {
    .nav-cart {
        min-height: 42px;
        padding: 0 12px;
        font-size: 9px;
    }


    .account-trigger {
        min-height: 43px;
        padding-right: 8px;
    }

    .profile-name {
        display: none;
    }

    .profile-chevron {
        margin-left: 1px;
    }

    .account-dropdown {
        right: -5px;
        width: min(305px, calc(100vw - 28px));
    }
}

@media (max-width: 700px) {
    .account-dropdown {
        top: calc(100% + 10px);
    }
}

@media (max-width: 480px) {
    .account-dropdown {
        width: min(292px, calc(100vw - 24px));
    }
}

/* Keep cart + account visible on the menu page. */
@media (max-width: 1050px) {
    .nav-actions {
        display: flex !important;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-left: auto;
        flex-shrink: 0;
    }
}

</style>

</head>

<body>

<?php
$active_page = "about";
include "navbar.php";
?>


<!-- =====================================================
     ABOUT HERO
===================================================== -->

<section class="about-hero">

    <div class="about-hero-content">

        <span class="about-eyebrow">
            WELCOME TO CAFELIA
        </span>

        <h1>
            More Than
            <span>Coffee.</span>
        </h1>

        <p>
            Cafelia is a place where good coffee,
            delicious treats, and meaningful moments
            come together.
        </p>

        <div class="about-hero-actions">

            <a href="menu.php" class="about-primary-btn">
                Explore Our Menu
                <span>→</span>
            </a>

            <a href="#our-story" class="about-secondary-btn">
                Discover Our Story
            </a>

        </div>

    </div>


    <div class="about-hero-visual">

        <div class="hero-coffee-glow"></div>

        <div class="hero-circle hero-circle-one"></div>

        <div class="hero-circle hero-circle-two"></div>


        <div class="about-hero-image">

            <img
                src="image/lady drinking coffee.png"
                alt="Woman enjoying coffee at Cafelia"
            >

        </div>


        <div class="about-floating-card">

            <div class="floating-icon">
                ☕
            </div>

            <div>

                <strong>
                    Made With Care
                </strong>

                <small>
                    Every cup, every time.
                </small>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     INTRO STRIP
===================================================== -->

<section class="about-intro-strip">

    <div class="about-intro-item">

        <strong>
            Quality
        </strong>

        <span>
            Ingredients
        </span>

    </div>


    <div class="about-intro-divider"></div>


    <div class="about-intro-item">

        <strong>
            Warm
        </strong>

        <span>
            Hospitality
        </span>

    </div>


    <div class="about-intro-divider"></div>


    <div class="about-intro-item">

        <strong>
            Good
        </strong>

        <span>
            Moments
        </span>

    </div>

</section>



<!-- =====================================================
     OUR STORY
===================================================== -->

<section class="about-story" id="our-story">

    <div class="story-image">

        <div class="story-image-frame">

            <img
                src="image/cafelia coffee shop.png"
                alt="Cafelia Coffee Shop"
            >

        </div>


        <div class="story-image-label">

            <span>
                CAFELIA
            </span>

            <small>
                Coffee & Moments
            </small>

        </div>

    </div>


    <div class="story-content">

        <span class="about-section-label">
            OUR STORY
        </span>

        <h2>
            Coffee Made for
            <span>Good Moments.</span>
        </h2>

        <p>
            Cafelia was created with a simple idea:
            coffee should be more than something you drink.
            It should be part of an experience.
        </p>

        <p>
            Whether you're starting your morning,
            taking a break from a busy day, catching up
            with friends, or simply enjoying some quiet time,
            Cafelia is a place where you can slow down
            and enjoy the moment.
        </p>

        <p>
            Every drink and treat is prepared with care,
            using quality ingredients and a genuine passion
            for creating a warm and enjoyable coffee
            experience.
        </p>


        <div class="story-highlight">

            <div class="story-quote">
                “
            </div>

            <div>

                <p>
                    Every cup is made to be part of
                    a moment worth remembering.
                </p>

                <span>
                    — The Cafelia Philosophy
                </span>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     MISSION / VISION / VALUES
===================================================== -->

<section class="beliefs-section">

    <div class="section-heading about-heading">

        <span>
            WHAT WE BELIEVE
        </span>

        <h2>
            The Heart Behind
            <strong>Cafelia</strong>
        </h2>

        <p>
            The principles that guide how we serve,
            create, and connect with our customers.
        </p>

    </div>


    <div class="beliefs-grid">


        <!-- MISSION -->

        <article class="belief-card">

            <div class="belief-top">

                <span class="belief-number">
                    01
                </span>

                <div class="belief-icon">
                    ☕
                </div>

            </div>

            <h3>
                Our Mission
            </h3>

            <p>
                To serve quality coffee and delicious
                treats while providing every customer
                with a welcoming and enjoyable experience.
            </p>

            <div class="belief-bottom">

                <span>
                    QUALITY & CARE
                </span>

                <span class="belief-arrow">
                    →
                </span>

            </div>

        </article>



        <!-- VISION -->

        <article class="belief-card featured-belief">

            <div class="belief-top">

                <span class="belief-number">
                    02
                </span>

                <div class="belief-icon">
                    ✦
                </div>

            </div>

            <h3>
                Our Vision
            </h3>

            <p>
                To become a trusted coffee destination
                where people can enjoy great coffee,
                good food, and memorable moments.
            </p>

            <div class="belief-bottom">

                <span>
                    GROW & INSPIRE
                </span>

                <span class="belief-arrow">
                    →
                </span>

            </div>

        </article>



        <!-- VALUES -->

        <article class="belief-card">

            <div class="belief-top">

                <span class="belief-number">
                    03
                </span>

                <div class="belief-icon">
                    ♥
                </div>

            </div>

            <h3>
                Our Values
            </h3>

            <p>
                Quality, hospitality, consistency,
                creativity, and genuine care for
                every customer who walks through our doors.
            </p>

            <div class="belief-bottom">

                <span>
                    SERVE WITH HEART
                </span>

                <span class="belief-arrow">
                    →
                </span>

            </div>

        </article>

    </div>

</section>



<!-- =====================================================
     CAFELIA EXPERIENCE
===================================================== -->

<section class="experience-section">

    <div class="experience-header">

        <div>

            <span class="about-section-label">
                WHY CAFELIA
            </span>

            <h2>
                The Cafelia
                <span>Experience.</span>
            </h2>

        </div>

        <p>
            From the first sip to the last bite,
            we want every visit to feel warm,
            simple, delicious, and worth coming back to.
        </p>

    </div>


    <div class="experience-grid">


        <!-- CARD 1 -->

        <article class="experience-card">

            <span class="experience-number">
                01
            </span>

            <div class="experience-icon">
                ☕
            </div>

            <h3>
                Quality Coffee
            </h3>

            <p>
                Carefully prepared coffee made to
                give you a satisfying and enjoyable
                experience with every cup.
            </p>

            <span class="experience-arrow">
                →
            </span>

        </article>



        <!-- CARD 2 -->

        <article class="experience-card">

            <span class="experience-number">
                02
            </span>

            <div class="experience-icon">
                🍰
            </div>

            <h3>
                Delicious Treats
            </h3>

            <p>
                Sweet and satisfying treats that
                perfectly complement your favorite
                Cafelia drink.
            </p>

            <span class="experience-arrow">
                →
            </span>

        </article>



        <!-- CARD 3 -->

        <article class="experience-card">

            <span class="experience-number">
                03
            </span>

            <div class="experience-icon">
                🤎
            </div>

            <h3>
                Warm Service
            </h3>

            <p>
                Friendly and welcoming service that
                makes every customer feel comfortable
                and appreciated.
            </p>

            <span class="experience-arrow">
                →
            </span>

        </article>



        <!-- CARD 4 -->

        <article class="experience-card">

            <span class="experience-number">
                04
            </span>

            <div class="experience-icon">
                ✨
            </div>

            <h3>
                Memorable Moments
            </h3>

            <p>
                A comfortable place for conversations,
                quiet breaks, productive mornings,
                and moments worth remembering.
            </p>

            <span class="experience-arrow">
                →
            </span>

        </article>

    </div>

</section>



<!-- =====================================================
     ABOUT CTA
===================================================== -->

<section class="about-cta">

    <div class="about-cta-decoration"></div>

    <div class="about-cta-content">

        <span>
            YOUR NEXT COFFEE AWAITS
        </span>

        <h2>
            Come Make a
            <em>Moment</em> With Us.
        </h2>

        <p>
            Discover your next favorite coffee,
            enjoy a delicious treat, and experience
            what makes Cafelia special.
        </p>

        <a href="menu.php" class="about-cta-button">

            Explore Our Menu

            <span>
                →
            </span>

        </a>

    </div>

</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="site-footer">

    <div class="footer-container">


        <!-- BRAND -->

        <div class="footer-brand">

            <a href="index.php" class="footer-logo">
                Cafelia
            </a>

            <p>
                Coffee, treats, and moments
                worth remembering.
            </p>

            <p class="footer-description">
                Crafted with passion, served with care,
                and made to brighten your everyday
                coffee moments.
            </p>


            <div class="footer-socials">

                <a href="#" aria-label="Facebook">
                    f
                </a>

                <a href="#" aria-label="Instagram">
                    ◎
                </a>

                <a href="#" aria-label="TikTok">
                    ♪
                </a>

            </div>

        </div>



        <!-- QUICK LINKS -->

        <div class="footer-column">

            <h3>
                Quick Links
            </h3>

            <ul>

                <li>
                    <a href="index.php">
                        Home
                    </a>
                </li>

                <li>
                    <a href="menu.php">
                        Menu
                    </a>
                </li>

                <li>
                    <a href="about.php">
                        About Us
                    </a>
                </li>

                <li>
                    <a href="why-us.php">
                        Why Cafelia?
                    </a>
                </li>

                <li>
                    <a href="contact.php">
                        Contact
                    </a>
                </li>

            </ul>

        </div>



        <!-- ACCOUNT -->

        <div class="footer-column">

            <h3>
                Account
            </h3>

            <ul>

                <li>
                    <a href="login.php">
                        Login
                    </a>
                </li>

                <li>
                    <a href="register.php">
                        Register
                    </a>
                </li>

                <li>
                    <a href="cart.php">
                        My Cart
                    </a>
                </li>

                <li>
                    <a href="orders.php">
                        My Orders
                    </a>
                </li>

            </ul>

        </div>



        <!-- CONTACT -->

        <div class="footer-column footer-contact">

            <h3>
                Visit Cafelia
            </h3>


            <div class="contact-item">

                <span class="contact-icon">
                    📍
                </span>

                <p>
                    Your favorite neighborhood
                    coffee shop
                </p>

            </div>


            <div class="contact-item">

                <span class="contact-icon">
                    ☎
                </span>

                <p>
                    +63 900 000 0000
                </p>

            </div>


            <div class="contact-item">

                <span class="contact-icon">
                    ✉
                </span>

                <p>
                    hello@cafelia.com
                </p>

            </div>


            <div class="contact-item">

                <span class="contact-icon">
                    ⏰
                </span>

                <p>
                    Mon – Sun<br>
                    8:00 AM – 9:00 PM
                </p>

            </div>

        </div>

    </div>



    <!-- FOOTER BOTTOM -->

    <div class="footer-bottom">

        <div class="footer-bottom-container">

            <p>
                © <?php echo date("Y"); ?>
                <strong>Cafelia</strong>.
                All Rights Reserved.
            </p>

            <div class="footer-legal">

                <a href="#">
                    Privacy Policy
                </a>

                <span>
                    •
                </span>

                <a href="#">
                    Terms & Conditions
                </a>

            </div>

        </div>

    </div>

</footer>



<script src="js/script.js"></script>

<script>
function toggleAccountMenu(button) {
    const menu = button.closest(".account-menu");
    const isOpen = menu.classList.toggle("open");
    button.setAttribute("aria-expanded", isOpen ? "true" : "false");
}

document.addEventListener("click", function(event) {
    document.querySelectorAll(".account-menu.open").forEach(function(menu) {
        if (!menu.contains(event.target)) {
            menu.classList.remove("open");

            const button = menu.querySelector(".account-trigger");
            if (button) {
                button.setAttribute("aria-expanded", "false");
            }
        }
    });
});

document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".account-menu.open").forEach(function(menu) {
            menu.classList.remove("open");

            const button = menu.querySelector(".account-trigger");
            if (button) {
                button.setAttribute("aria-expanded", "false");
            }
        });
    }
});
</script>

</body>

</html>