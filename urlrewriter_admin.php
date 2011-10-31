<?php
require_once 'pre.php';
require_once 'auth.php';
?>
<div id="contenu_int" class="urlrewriter_container">
<?php
require_once realpath(dirname(__FILE__)) . '/Urlrewriter.class.php';
$urlrewriterObj = new Urlrewriter();
$urlrewriterVars = $urlrewriterObj->verifForms();
?>
<p align="left">
	<a href="accueil.php" class="lien04">Accueil </a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module_liste.php" class="lien04">Liste des modules</a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module.php?nom=urlrewriter" class="lien04">Url rewriter</a>
</p>

<div class="entete_liste">
	<div class="titre">URL REWRITER</div>
</div>
<?php 
// Message d'erreur ?
if($urlrewriterObj->error != '') {
    echo '<div class="urlrewriter_error">' . $urlrewriterObj->error . '</div>';
}

include_once realpath(dirname(__FILE__)) . '/forms/etape' . $urlrewriterVars['etape'] . '.php';
?>
</div> <!-- /#contenu_int -->