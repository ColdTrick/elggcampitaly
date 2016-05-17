<?php

$guid = (int) get_input('guid');
$entity = get_entity($guid);
if (!($entity instanceof ElggBlog)) {
	register_error(elgg_echo(''));
	forward(REFERER);
}

if ($entity->elggcampitaly_featured) {
	// is already featured, so unfeature
	unset($entity->elggcampitaly_featured);
	
	system_message(elgg_echo('elggcampitaly:action:toggle_feature:success:unfeature'));
} else {
	// feature the blog
	$entity->elggcampitaly_featured = time();

	// let others know we featured the blog
	elgg_trigger_event('feature', 'object', $entity);
	
	system_message(elgg_echo('elggcampitaly:action:toggle_feature:success:feature'));
}

forward(REFERER);
