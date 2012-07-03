<?php
require_once 'pre.php';
require_once 'auth.php';
autorisation("urlrewriter");
?>
<p>Les URLs ci-dessous ont été enregistrées :</p>
<?php
foreach((array) $urlrewriterVars['rewritings'] as $objet => $rewritings) {
	?>
	<table id="urlrewriter_recap">
    <caption>Mise à jour des URL pour l'objet '<?php echo ucfirst($objet); ?>'</caption>
	<thead>
	<tr>
        <th>ID</th>
		<th>Langue</th>
		<th>Url rewritée</th>
	</tr>
	</thead>
	<tbody>
	<?php 
	foreach((array) $rewritings as $rewriting) {
		?>
		<tr>
            <td><?php echo $rewriting['context']['ID'];?></td>
			<td><?php echo $rewriting['context']['lang'];?></td>
			<td><?php echo $rewriting['urlRewrited']; ?></td>
		</tr>
		<?php	
	}
	?>
	</tbody>
	</table>
	<?php
}