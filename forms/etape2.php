<?php
require_once 'pre.php';
require_once 'auth.php';
//var_dump($urlrewriterVars['rewritings']);

foreach((array) $urlrewriterVars['rewritings'] as $objet => $rewritings) {
	?>
	<form action="" method="post">
	<p style="text-align:right">
		<input type="hidden" name="urlrewriter_step" value="2"/>
		<input type="submit" name="" value="Valider"/>
	</p>
	<table summary="<?php echo $objet; ?>" id="urlrewriter_recap">
	<thead>
	<tr>
		<th>Contexte</th>
		<th>Url rewritée</th>
		<th>Url normale</th>
	</tr>
	</thead>
	<tbody>
	<?php 
	foreach((array) $rewritings as $rewriting) {
		?>
		<tr>
			<td>
				<?php 
				foreach((array) $rewriting['context'] as $key => $context) {
					echo $key . ' : ' . $context . '<br/>';
				}
				?>
			</td>
			<td><?php echo $rewriting['urlRewrited']; ?></td>
			<td><?php echo $rewriting['urlNormal']; ?></td>
		</tr>
		<?php	
	}
	?>
	</tbody>
	</table>
	<p style="text-align:right"><input type="submit" name="" value="Valider"/></p>
	</form>
	<?php
}