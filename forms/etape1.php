<?php
require_once 'pre.php';
require_once 'auth.php';

$tokens = $urlrewriterObj->retrieveTokens();
foreach ((array) $tokens as $objet => $tokens) {
	?>
	<h3><?php echo ucfirst($objet); ?></h3>
	<form action="" method="post">
	<p>
		<input type="hidden" name="urlrewriter_step" value="1"/>
		<input type="text" name="urlrewriter_rewrite[<?php echo $objet; ?>]" value=""/>
		<input type="submit" value="OK"/>
	</p>
	</form>
	
	<p>
		<?php
		foreach((array) $tokens as $key => $token) {
			echo '%' . $key . '% : ' . $token['label'] . '<br/>';
		}
		?>
	</p>
	<hr/>
	<?php
}