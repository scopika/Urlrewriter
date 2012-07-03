<?php
if(empty($urlrewriter_template_vars)) die('Ce fichier ne peut être appellé directement');
require_once realpath(dirname(__FILE__)) . '/Urlrewriter.class.php';
$urlrewriter = new Urlrewriter();
//$forcedUrl  = $urlrewriter->getObjectForcedUrl($urlrewriter_template_vars['type'], $urlrewriter_template_vars['objectId'], $lang);
$forcedUrl  = '';
$tokens     = $urlrewriter->getTokens($urlrewriter_template_vars['type']);
$urlRewriterOptions =  unserialize(Variable::lire('urlrewriter_params'));
?>

<script type="text/javascript">
// On cache l'ancien champs 'url réécrite' de Thelia
$(document).ready(function() {
    $('input[name="urlreecrite"]').parents('ul:first').hide();
})
</script>
<div class="entete">
    <div class="titre" style="cursor:pointer" onclick="$('#urlrewriter_pliant').show('slow');">URLREWRITING <span class="scopika_credits">par <a href="http://scopika.com">Scopika</a></span></div>
    <div class="fonction_valider"><a href="#" onclick="javascript:document.getElementById('formulaire').submit(); return false;">VALIDER LES MODIFICATIONS</a></div>
</div>
<div class="blocs_pliants_prod urlrewriter" id="urlrewriter_pliant">
    <table width="100%" cellpadding="5" cellspacing="0">
        <tbody>
        <tr>
            <td>URL classique</td>
            <td>
                <a href="<?php echo $urlrewriter->getBasicUrl($urlrewriter_template_vars['type'], $urlrewriter_template_vars['objectId'], $lang);; ?>" title="">
                    <?php echo $urlrewriter->getBasicUrl($urlrewriter_template_vars['type'], $urlrewriter_template_vars['objectId'], $lang, false); ?>
                </a>
            </td>
        </tr>
        <?php
        // Si aucun motif de rewriting n'est défini pour ce type d'objet, STOP!
        if(empty($urlRewriterOptions['rules'][$urlrewriter_template_vars['type']])) {
            ?>
            <tr>
                <td colspan="2" style="text-align:center; color:red; font-weight:bold">
                    Le plugin de rewriting doit tout d'abord être paramétré. Merci de renseigner un motif de rewriting sur <a href="module.php?nom=urlrewriter" title="">la page de configuration du plugin</a>
                </td>
            </tr>
            <?php
        } else { ?>
            <tr>
                <td>URL rewritée</td>
                <td>
                    <a href="<?php echo $urlrewriter->getRewritedUrl($urlrewriter_template_vars['type'], $urlrewriter_template_vars['objectId'], $lang); ?>" title="">
                        <?php echo $urlrewriter->getRewritedUrl($urlrewriter_template_vars['type'], $urlrewriter_template_vars['objectId'], $lang, false, false); ?>
                    </a>
                    <?php
                    if(in_array($urlrewriter_template_vars['type'], array('produit', 'contenu'))) {
                        switch($urlrewriter_template_vars['type']) {
                            case 'produit' : $urlrewriterOriginalParent = $produit->id; break;
                            case 'contenu' : $urlrewriterOriginalParent = $contenu->id; break;
                        }
                        ?>
                        <input type="hidden" name="urlrewriter_object_originalParent" value="<?php echo $urlrewriterOriginalParent; ?>" />
                        <?php
                    } ?>
                </td>
            </tr>
            <tr>
                <td>Motif de rewriting principal</td>
                <td><?php echo $urlRewriterOptions['rules'][$urlrewriter_template_vars['type']]; ?></td>
            </tr>
            <tr >
                <td>Motif de rewriting personnalisé<br/>(Laissez vide pour utiliser le motif de rewriting principal)</td>
                <td>
                    <p class="urlrewriter_notice">Cette fonctionnalité n'est disponible que dans <a href="mailto:contact@scopika.com">la version premium</a> du plugin Urlrewriter</p>
                    <input name="" type="text"  value="" disabled="disabled">
                    <?php if(!empty($tokens)) : ?>
                        <h4 style="color:#3B4B5B">Motifs disponibles :</h4>
                        <ul class="urlrewriter_tokenlist">
                            <?php
                            foreach((array) $tokens as $token) {
                                echo '<li><span class="urlrewriter_token">%' . $token::getToken() . '%</span> : ' . $token::getDescription() . '</li>';
                            }
                            ?>
                        </ul>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
            if(in_array($urlrewriter_template_vars['type'], array('rubrique', 'dossier'))) {
                ?>
                <tr>
                    <td>Exclure</td>
                    <td>
                        <p class="urlrewriter_notice">Cette fonctionnalité n'est disponible que dans <a href="mailto:contact@scopika.com">la version premium</a> du plugin Urlrewriter.</p>
                        <input name="" type="checkbox"  value="true" disabled="disabled"/>
                        <?php
                        switch($urlrewriter_template_vars['type']) {
                            case 'rubrique' :
                                echo "<br/>Si cette case est cochée, cette rubrique sera ignorée du calcul des URL de ses sous-rubriques et produits, notamment lors de l'utilisation d'URL arborescentes avec le marqueur %arbo%";
                                break;
                            case 'dossier' :
                                echo "<br/>Si cette case est cochée, ce dossier sera ignoré du calcul des URL de ses sous-dossier et contenus, notamment lors de l'utilisation d'URL arborescentes avec le marqueur %arbo%";
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Recalculer</td>
                    <td>
                        <p class="urlrewriter_notice">Cette fonctionnalité n'est disponible que dans <a href="mailto:contact@scopika.com">la version premium</a> du plugin Urlrewriter</p>
                        <input name="" type="checkbox"  value="true" class="form" disabled="disabled"/>
                        <?php
                        switch($urlrewriter_template_vars['type']) {
                            case 'rubrique' :
                                echo '<br/>Si cette case est cochée, les URL des sous-rubriques et des produits seront recalculées.';
                                break;
                            case 'dossier' :
                                echo '<br/>Si cette case est cochée, les URL des sous-dossiers et des contenus seront recalculées';
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        } // Fin de la vérif du paramétrage du plugin ?>
        </tbody>
    </table>
    <div class="bloc_fleche" style="cursor:pointer" onclick="$('#urlrewriter_pliant').hide();"><img src="gfx/fleche_accordeon_up.gif"></div>
</div>