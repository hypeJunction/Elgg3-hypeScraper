<?php

echo elgg_view_form('embed/player', [
	'class' => 'elgg-form-embed-player',
		], $vars);
?>
<script>
	require(['embed/tab/player']);
</script>
