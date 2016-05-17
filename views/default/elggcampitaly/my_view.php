<?php

$entity = elgg_extract('entity', $vars);
$full_view = (bool) elgg_extract('full_view', $vars, false);

if (!($entity instanceof ElggBlog)) {
	// not a blog
	return;
}

if ($full_view) {
	// don't do anything on the full view of a blog
	return;
}

if (!$entity->elggcampitaly_featured) {
	return;
}

echo elgg_format_element('div', ['class' => 'elgg-quiet'], elgg_echo('elggcampitaly:object:blog:featured'));
