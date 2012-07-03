<?php
require_once 'pre.php';
require_once 'auth.php';
autorisation("urlrewriter");

$variable = new Variable('urlrewriter_params');
$pluginParams = unserialize($variable->valeur);
$tokens = $urlrewriterObj->getTokens();
?>

<p class="urlrewriter_warning"><strong>Avant toute modification, nous vous conseillons d'effectuer une sauvegarde de votre base de données.</strong></p>
<?php
foreach ((array) $tokens as $objet => $tokens) {
	$rule = '';
	if(!empty($pluginParams['rules'][$objet])) $rule = $pluginParams['rules'][$objet];
	?>
	<h2 class="urlrewriter_h2"><?php echo ucfirst($objet); ?></h2>

	<form action="" method="post">
	<p>
		<input type="hidden" name="urlrewriter_step" value="1"/>
        <input type="hidden" name="urlrewriter_object" value="<?php echo $objet; ?>"/>
		<label for="urlrewriter_rule">Motif de rewriting :</label>
		<input type="text" name="urlrewriter_rule" value="<?php echo $rule; ?>" id="urlrewriter_rule" class="urlrewriter_inputToken"/>
        <input type="submit" value="Enregistrer et recalculer les URL"/>
        <br/>Ex : %arbo%/%titre%/
	</p>
	</form>

    <h3>Motifs disponibles :</h3>
    <p class="urlrewriter_notice">
        <a href="mailto:contact@scopika.com">La version premium</a> du plugin ajoute d'autres motifs de rewriting puissants : <br/>
        <strong>%arbo%</strong> : pour générer des URL arborescentes<br/>
        <strong>%lang%</strong> : pour générer des URL dépendantes de la langue. Ex : http://monsite.com/fr/ma-page, http://monsite.com/en/my-page
    </p>
    <ul class="urlrewriter_tokenlist">
        <?php
        foreach((array) $tokens as $token) {
            echo '<li><span class="urlrewriter_token">%' . $token->getToken() . '%</span> : ' . $token->getDescription() . '</li>';
        }
        ?>
    </ul>
	<?php
}