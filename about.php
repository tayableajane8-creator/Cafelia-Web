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
   CAFELIA SHARED NAVBAR
   Matches the homepage / menu navigation
========================================================= */

.navbar {
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
    width: 100%;
    background: rgba(36, 21, 15, 0.97) !important;
    border-bottom: 1px solid rgba(214, 173, 130, 0.18) !important;
    box-shadow: 0 8px 30px rgba(20, 10, 5, 0.12) !important;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}

.navbar .nav-container {
    width: 100%;
    max-width: 1380px;
    min-height: 82px;
    margin: 0 auto;
    padding: 0 42px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar .logo {
    width: auto !important;
    min-width: 145px;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center;
    text-decoration: none;
}

.navbar .logo-text {
    display: inline-block;
    font-family: "Playfair Display", Georgia, "Times New Roman", serif;
    font-size: 27px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 4px;
    color: #f8f1e8 !important;
    white-space: nowrap;
    transition: color .25s ease, transform .25s ease;
}

.navbar .logo:hover .logo-text {
    color: #d8b892 !important;
    transform: translateY(-1px);
}

.navbar .nav-menu {
    display: flex;
    align-items: center;
    gap: 34px;
    margin-left: auto;
    margin-right: 30px;
}

.navbar .nav-menu a {
    position: relative;
    padding: 0 !important;
    color: rgba(255,255,255,0.88) !important;
    background: transparent !important;
    border: 0 !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1.25px;
    line-height: 1;
    text-decoration: none;
    text-transform: uppercase;
    transition: color .25s ease;
}

.navbar .nav-menu a:hover,
.navbar .nav-menu a.active {
    color: #d8b892 !important;
}

.navbar .nav-menu a.active::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: -8px;
    width: 100%;
    height: 2px;
    border-radius: 2px;
    background: #d8b892;
}

.navbar .nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.navbar .nav-actions a {
    min-height: 38px;
    padding: 9px 15px !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    font-size: 10px !important;
    font-weight: 700 !important;
    letter-spacing: 1px;
    text-decoration: none;
    text-transform: uppercase;
    transition: all .25s ease;
}

.navbar .nav-cart {
    border: 1px solid rgba(214,173,130,0.55) !important;
    color: #fff !important;
    background: transparent !important;
}

.navbar .nav-login {
    border: 1px solid #d6ad82 !important;
    background: #d6ad82 !important;
    color: #2a1811 !important;
}

.navbar .nav-cart:hover {
    border-color: #d8b892 !important;
    color: #d8b892 !important;
    transform: translateY(-1px);
}

.navbar .nav-login:hover {
    background: #e1bd91 !important;
    border-color: #e1bd91 !important;
    color: #2a1811 !important;
    transform: translateY(-1px);
}

/* ---------- SHARED GLASS ACCOUNT / CART ---------- */

.navbar .nav-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-left: auto;
}

.navbar .nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.navbar .nav-actions a.nav-cart {
    min-height: 44px;
    padding: 0 17px !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 1px solid rgba(255,255,255,.18) !important;
    border-radius: 999px;
    background: rgba(255,255,255,.055) !important;
    color: rgba(255,250,243,.92) !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: .14em;
    box-shadow: 0 8px 22px rgba(0,0,0,.12),
                inset 0 1px 0 rgba(255,255,255,.10);
    backdrop-filter: blur(14px) saturate(135%);
    -webkit-backdrop-filter: blur(14px) saturate(135%);
    transition: all .22s ease;
}

.navbar .nav-actions a.nav-cart:hover {
    border-color: rgba(216,163,109,.42) !important;
    background: rgba(216,163,109,.10) !important;
    color: #fffaf3 !important;
    transform: translateY(-1px);
}

.cart-icon {
    font-size: 14px;
    line-height: 1;
    filter: saturate(.75);
}

.account-menu {
    position: relative;
}

.account-trigger {
    position: relative;
    min-height: 46px;
    padding: 5px 15px 5px 6px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(255,255,255,.16) !important;
    border-radius: 999px;
    background: linear-gradient(135deg,rgba(255,255,255,.105),rgba(255,255,255,.035)) !important;
    color: #fffaf3 !important;
    text-decoration: none !important;
    cursor: pointer;
    backdrop-filter: blur(20px) saturate(140%);
    -webkit-backdrop-filter: blur(20px) saturate(140%);
    box-shadow: 0 8px 26px rgba(0,0,0,.16),
                inset 0 1px 0 rgba(255,255,255,.12);
    transition: .22s ease;
}

.account-trigger:hover,
.account-trigger[aria-expanded="true"] {
    border-color: rgba(216,163,109,.42) !important;
    background: linear-gradient(135deg,rgba(255,255,255,.15),rgba(216,163,109,.07)) !important;
    box-shadow: 0 12px 32px rgba(0,0,0,.21),
                0 0 0 4px rgba(216,163,109,.045);
    transform: translateY(-1px);
}

.profile-avatar,
.dropdown-avatar {
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 50%;
    color: #fffaf3;
    background: linear-gradient(145deg,#c99968,#9c623a);
    border: 1px solid rgba(255,255,255,.30);
    box-shadow: 0 4px 12px rgba(0,0,0,.20),
                inset 0 1px 0 rgba(255,255,255,.25);
    font-family: "Playfair Display",Georgia,serif;
    font-weight: 700;
}

.profile-avatar {
    width: 36px;
    height: 36px;
    font-size: 14px;
}

.account-login-trigger {
    width: 46px;
    padding: 5px !important;
    justify-content: center;
}

.account-login-trigger .profile-avatar {
    width: 36px;
    height: 36px;
}

.profile-name {
    max-width: 150px;
    color: rgba(255,250,243,.94);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    background: linear-gradient(145deg,rgba(61,37,26,.84),rgba(29,18,13,.94));
    backdrop-filter: blur(26px) saturate(145%);
    -webkit-backdrop-filter: blur(26px) saturate(145%);
    box-shadow: 0 26px 65px rgba(0,0,0,.34),
                inset 0 1px 0 rgba(255,255,255,.12);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px) scale(.975);
    transform-origin: top right;
    pointer-events: none;
    transition: .2s ease;
}

.account-menu.open .account-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

.dropdown-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 10px 14px;
    color: #fffaf3;
}

.dropdown-profile > div {
    min-width: 0;
}

.dropdown-profile strong,
.dropdown-profile small,
.dropdown-item strong,
.dropdown-item small {
    display: block;
}

.dropdown-profile strong {
    font-size: 13px;
}

.dropdown-profile small {
    margin-top: 3px;
    color: rgba(255,250,243,.55);
    font-size: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-avatar {
    width: 42px;
    height: 42px;
    font-size: 16px;
}

.dropdown-divider {
    height: 1px;
    margin: 3px 5px 7px;
    background: linear-gradient(90deg,transparent,rgba(255,255,255,.12),transparent);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px;
    margin: 2px 0;
    border: 1px solid transparent;
    border-radius: 13px;
    color: #fffaf3 !important;
    text-decoration: none !important;
    transition: .18s ease;
}

.dropdown-item:hover {
    background: rgba(255,255,255,.055);
    border-color: rgba(255,255,255,.08);
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
}

.dropdown-item strong {
    font-size: 11px;
}

.dropdown-item small {
    margin-top: 2px;
    color: rgba(255,250,243,.48);
    font-size: 9px;
}

.dropdown-logout {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    color: rgba(255,225,217,.80) !important;
    text-decoration: none !important;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
}

.dropdown-logout:hover {
    color: #fffaf3 !important;
}

@media (max-width: 1050px) {
    .navbar .nav-container {
        padding: 0 25px;
    }

    .navbar .nav-menu {
        gap: 20px;
        margin-right: 18px;
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

    .navbar .nav-actions {
        margin-left: auto;
    }
}

@media (max-width: 520px) {
    .navbar .logo-text {
        font-size: 22px;
        letter-spacing: 3px;
    }

    .navbar .nav-menu {
        gap: 13px;
    }

    .navbar .nav-menu a {
        font-size: 9px !important;
        letter-spacing: 1px;
    }

    .navbar .nav-actions a {
        min-height: 34px;
        padding: 8px 12px !important;
        font-size: 9px !important;
    }
}
</style>

<style>
@media (max-width: 820px) {
    .navbar .nav-right {
        margin-left: auto;
    }

    .navbar .nav-actions {
        margin-left: 0;
        width: auto;
        justify-content: flex-end;
    }
}

@media (max-width: 600px) {
    .navbar .nav-right {
        width: auto;
        margin-left: auto;
        gap: 7px;
    }

    .navbar .nav-actions {
        width: auto;
        margin: 0;
    }

    .navbar .nav-actions a.nav-cart {
        min-height: 40px;
        padding: 0 12px !important;
        font-size: 9px !important;
    }

    .account-trigger {
        min-height: 42px;
    }

    .account-login-trigger {
        width: 42px;
    }

    .profile-name {
        display: none;
    }

    .account-dropdown {
        right: -4px;
        width: min(305px, calc(100vw - 20px));
    }
}
</style>


<style>
/* =========================================================
   ABOUT PAGE — MATCH MENU NAVBAR EXACTLY
   ========================================================= */

.navbar {
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
}

.navbar .nav-container {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    width: 100% !important;
}

.navbar .logo {
    margin-right: auto !important;
}

.navbar .nav-menu {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    margin-left: auto !important;
}

.navbar .nav-menu a {
    position: relative !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    text-decoration: none !important;
    text-transform: uppercase !important;
}

.navbar .nav-menu a.active::after {
    bottom: -8px !important;
    height: 2px !important;
}

.navbar .nav-actions {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 10px !important;
    margin-left: 22px !important;
    flex-shrink: 0 !important;
}

.navbar .nav-cart {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    min-height: 44px !important;
    padding: 0 17px !important;
    border: 1px solid rgba(255,255,255,.18) !important;
    border-radius: 999px !important;
    background: rgba(255,255,255,.055) !important;
    color: rgba(255,250,243,.92) !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: .14em !important;
    box-shadow:
        0 8px 22px rgba(0,0,0,.12),
        inset 0 1px 0 rgba(255,255,255,.10) !important;
    backdrop-filter: blur(14px) saturate(135%) !important;
    -webkit-backdrop-filter: blur(14px) saturate(135%) !important;
}

.navbar .account-trigger {
    appearance: none !important;
    -webkit-appearance: none !important;
    position: relative !important;
    min-height: 48px !important;
    padding: 5px 12px 5px 6px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 10px !important;
    border: 1px solid rgba(255,255,255,.16) !important;
    border-radius: 999px !important;
    background: linear-gradient(135deg,rgba(255,255,255,.105),rgba(255,255,255,.035)) !important;
    color: #fffaf3 !important;
    font-family: inherit !important;
    cursor: pointer !important;
    backdrop-filter: blur(20px) saturate(140%) !important;
    -webkit-backdrop-filter: blur(20px) saturate(140%) !important;
    box-shadow:
        0 8px 26px rgba(0,0,0,.16),
        inset 0 1px 0 rgba(255,255,255,.12) !important;
}

.navbar .profile-avatar {
    width: 36px !important;
    height: 36px !important;
    display: grid !important;
    place-items: center !important;
    flex: 0 0 auto !important;
    border-radius: 50% !important;
    color: #fffaf3 !important;
    background: linear-gradient(145deg,#c99968 0%,#9c623a 100%) !important;
    border: 1px solid rgba(255,255,255,.30) !important;
    box-shadow:
        0 4px 12px rgba(0,0,0,.20),
        inset 0 1px 0 rgba(255,255,255,.25) !important;
    font-family: "Playfair Display",Georgia,serif !important;
    font-weight: 700 !important;
    font-size: 14px !important;
}

.navbar .profile-name {
    max-width: 140px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    color: rgba(255,250,243,.94) !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: .025em !important;
}

.navbar .account-login-icon {
    min-width: 48px !important;
    padding: 5px 6px !important;
    text-decoration: none !important;
    justify-content: center !important;
}

.navbar .profile-avatar-guest {
    width: 36px !important;
    height: 36px !important;
    font-family: Arial,sans-serif !important;
    font-size: 19px !important;
}

@media (max-width: 1050px) {
    .navbar .nav-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        margin-left: auto !important;
        flex-shrink: 0 !important;
    }
}

@media (max-width: 900px) {
    .navbar .nav-cart {
        min-height: 42px !important;
        padding: 0 12px !important;
        font-size: 9px !important;
    }

    .navbar .account-trigger {
        min-height: 43px !important;
        padding-right: 8px !important;
    }

    .navbar .profile-name {
        display: none !important;
    }
}

@media (max-width: 820px) {
    .navbar .nav-container {
        flex-wrap: wrap !important;
        gap: 15px !important;
    }

    .navbar .nav-menu {
        order: 3 !important;
        width: 100% !important;
        margin: 0 !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
        gap: 18px !important;
    }

    .navbar .nav-menu a {
        padding: 8px 0 !important;
    }

    .navbar .nav-menu a.active::after {
        bottom: 0 !important;
    }

    .navbar .nav-actions {
        margin-left: auto !important;
    }
}

@media (max-width: 520px) {
    .navbar .nav-menu {
        gap: 13px !important;
    }

    .navbar .nav-menu a {
        font-size: 9px !important;
        letter-spacing: 1px !important;
    }

    .navbar .nav-actions a {
        min-height: 34px !important;
        padding: 8px 12px !important;
        font-size: 9px !important;
    }
    /* Remove unwanted navbar decoration beside the logo */
.navbar .logo::before,
.navbar .logo::after {
    content: none !important;
    display: none !important;
}
}
</style>

</head>

<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<header class="navbar">
    <div class="nav-container">

        <a href="index.php" class="logo" aria-label="Cafelia Home">
            <span class="logo-text">CAFELIA</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php">HOME</a>
            <a href="menu.php">MENU</a>
            <a href="about.php" class="active">ABOUT US</a>
            <a href="join_team.php">JOIN OUR TEAM</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <div class="nav-actions">

            <a href="cart.php" class="nav-cart" aria-label="Open shopping cart">
                <span class="cart-icon">🛒</span>
                <span>CART</span>
            </a>

            <div class="account-menu">

                <?php if (isset($_SESSION["user_id"])): ?>

                    <button type="button"
                            class="account-trigger"
                            aria-expanded="false"
                            aria-haspopup="true"
                            onclick="toggleAccountMenu(this)">
                        <span class="profile-avatar">
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
                                <?php echo htmlspecialchars(strtoupper(substr($nav_name, 0, 1))); ?>
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

                        <a href="logout.php" class="dropdown-logout">
                            <span>↪</span>
                            Log Out
                        </a>

                    </div>

                <?php else: ?>

                    <a href="login.php"
                       class="account-trigger account-login-icon"
                       aria-label="Log in to your Cafelia account">
                        <span class="profile-avatar profile-avatar-guest">♙</span>
                    </a>

                <?php endif; ?>

            </div>
        </div>

    </div>
</header>



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