<?php
require_once 'pre.php';
require_once 'auth.php';
autorisation("urlrewriter");

$variable = new Variable('urlrewriter_params');
$pluginParams = unserialize($variable->valeur);

$tokens = $urlrewriterObj->retrieveTokens();
foreach ((array) $tokens as $objet => $tokens) {
	echo $rule = '';
	if(!empty($pluginParams['rules'][$objet])) $rule = $pluginParams['rules'][$objet];	
	?>
	<h2 class="urlrewriter_h2"><?php echo ucfirst($objet); ?></h2>
	
	<h3>Motifs disponibles :</h3> 
	<ul class="urlrewriter_tokenlist">
	<?php
	foreach((array) $tokens as $key => $token) {
		echo '<li><span class="urlrewriter_token">%' . $key . '%</span> : ' . $token['label'] . '</li>';
	}
	?>
	</ul>
	
	<form action="" method="post">
	<p>
		<input type="hidden" name="urlrewriter_step" value="1"/>
		<label for="urlrewriter_rewrite[<?php echo $objet; ?>]">Motif de rewriting :</label>
		<input type="text" name="urlrewriter_rewrite[<?php echo $objet; ?>]" value="<?php echo $rule; ?>" id="urlrewriter_rewrite[<?php echo $objet; ?>]" class="urlrewriter_inputToken"/>
	</p>
	<p>
		<label for="urlrewriter_process[<?php echo $objet; ?>]">Recalculer les URL maintenant</label>
		<input type="checkbox" name="urlrewriter_process[<?php echo $objet; ?>]" id="urlrewriter_process[<?php echo $objet; ?>]" value="1"/>
		<br/><input type="submit" value="Enregistrer"/>
	</p>
	</form>	
	<?php
}