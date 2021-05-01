<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="fr">
  <head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="CoolHome - La Domotique à l'oeil">
    <meta name="author" content="Xavier Péchoultres">
    <meta name="keyword" content="">
	<title><?= $title ?></title>
	<?php /*
    <link rel="apple-touch-icon" sizes="57x57" href="assets/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="assets/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="assets/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="assets/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="assets/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="assets/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="assets/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/favicon/manifest.json">
    */ ?>
	<link rel="icon" href="/img/favicon2.png" type="image/png"/>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="assets/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!-- Main styles for this application-->
    <link href="/coreui/css/style.css" rel="stylesheet">
  	<link href="/coreui/chartjs/css/coreui-chartjs.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
	
	<link rel="stylesheet" href="/vendor/fortawesome/font-awesome/css/all.min.css">
	<!-- datatables -->
	<link rel="stylesheet" href="/vendor/datatables/datatables/media/css/dataTables.bootstrap4.min.css">
  	
  	<script src="/vendor/components/jquery/jquery.min.js"></script>
	<script src="/vendor/components/jqueryui/jquery-ui.js"></script>
  </head>
  <body class="c-app <?= (isset($body_class) ? $body_class : '') ?>">
