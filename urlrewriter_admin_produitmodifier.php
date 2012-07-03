<?php
require_once 'pre.php';
require_once 'auth.php';
include_once realpath(dirname(__FILE__)) . '/../../../fonctions/authplugins.php';
autorisation('urlrewriter');

// Si aucune ref n'est transmise, ou si le produit n'existe pas, on arrête là le massacre!
if(empty($_REQUEST['ref'])) return false;
$produit = new Produit();
if(!$produit->charger($_REQUEST['ref'])) return false;

// langue
$lang=1;
if(!empty($_GET['lang'])) $lang=$_GET['lang'];

$urlrewriter_template_vars = array(
    'type' => 'produit',
    'objectId' => $produit->id
);

require realpath(dirname(__FILE__)) . '/urlrewriter_admin_modifier.php';