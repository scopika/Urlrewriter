<?php
require_once 'pre.php';
require_once 'auth.php';
?>
<div id="contenu_int" class="urlrewriter_container">
<?php
require_once realpath(dirname(__FILE__)) . '/Urlrewriter.class.php';
$urlrewriterObj = new Urlrewriter();

function urlrewriter_verifFormStep1() {
    global $urlrewriterObj;

    $rewritings = array(); // tableau qui contiendra toutes les URLs calculées
    $langues = $urlrewriterObj->getLanguages();
    if(empty($_POST['urlrewriter_rule']) || empty($_POST['urlrewriter_object'])) return false;

    $regle = $_POST['urlrewriter_rule'];
    $type = $_POST['urlrewriter_object'];
    $tokens = $urlrewriterObj->getTokens($type);
    if(empty($tokens)) return false;

    if($urlrewriterObj->verifyObjectRule($type, $regle) === false) return false;

    // Enregistrement en BDD des nouvelles règles
    $variable = new Variable('urlrewriter_params');
    $pluginParams = unserialize($variable->valeur);
    $pluginParams['rules'][$type] = $regle;
    $variable->valeur = serialize($pluginParams);
    $variable->maj();

    $objects = $urlrewriterObj->getObjects($type);
    foreach((array) $objects as $row) {
        foreach($langues as $lang) {
            $newUrl = $urlrewriterObj->getRewritedUrl($type, $row['id'], $lang->id, true, false);
            $newUrl = $urlrewriterObj->updateObjectUrl($type, $row['id'], $newUrl, $lang->id);
            $rewritings[$type][] = array(
                'urlRewrited' => $newUrl,
                'context' => array(
                    'lang' => $lang->id,
                    'ID' => $row['id']
                )
            );
        }
    }

    $_SESSION['urlrewriter']['rewritings'] = $rewritings;
    return $rewritings;
}


$urlrewriterVars = array('etape' => 1);
switch(intval($_POST['urlrewriter_step'])) {
    case 1 :
        $rewritings = urlrewriter_verifFormStep1();
        if(!empty($rewritings) && $rewritings != false) $urlrewriterVars['etape'] = 2;
        $urlrewriterVars['rewritings'] = $rewritings;
        unset($rewritings);
        break;
}
if(!file_exists(realpath(dirname(__FILE__)) . '/forms/etape' . $res['etape'] . '.php')) $res['etape'] = 1;

?>
<p align="left">
	<a href="accueil.php" class="lien04">Accueil </a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module_liste.php" class="lien04">Liste des modules</a> <img src="gfx/suivant.gif" width="12" height="9" border="0" />
	<a href="module.php?nom=urlrewriter" class="lien04">Url rewriter</a>
</p>

<div class="entete_liste">
	<div class="titre">URL REWRITER <span class="scopika_credits">par <a href="http://scopika.com">Scopika</a></span></div>
</div>
<?php 
// Message d'erreur ?
if($urlrewriterObj->error != '') {
    echo '<div class="urlrewriter_error">' . $urlrewriterObj->error . '</div>';
}

include_once realpath(dirname(__FILE__)) . '/forms/etape' . $urlrewriterVars['etape'] . '.php';
?>
</div> <!-- /#contenu_int -->