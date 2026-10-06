<?php
/* Kopf wie auf der statischen Seite: Assets und Masthead aus vorlagen/ (deploy/wp-theme.mjs). */
?><!doctype html>
<html lang="de" data-page="<?php echo is_front_page() ? 'home v20' : 'v20'; ?>" data-werbung="an">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#ffffff">
<meta name="color-scheme" content="light">
<?php echo ma21_kopf_assets(); ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Zum Inhalt springen</a>
<?php echo ma21_kopf(is_front_page() ? 'kopf.html' : 'kopf-seite.html'); ?>
<main id="main" tabindex="-1">
