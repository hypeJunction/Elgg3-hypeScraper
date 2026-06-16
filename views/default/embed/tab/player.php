<?php

echo elgg_view_form('embed/player', [
	'class' => 'elgg-form-embed-player',
		], $vars);

elgg_import_esm('embed/tab/player');
