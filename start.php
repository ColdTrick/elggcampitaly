<?php

// register default Elgg event
elgg_register_event_handler('init', 'system', 'elggcampitaly_init');

/**
 * Called during system init
 *
 * @return void
 */
function elggcampitaly_init() {
	
	// register a site menu item
	elgg_register_menu_item('site', [
		'name' => 'elggcampitaly',
		'text' => elgg_echo('elggcampitaly:site:menuitem'),
		'href' => 'blog/all',
	]);
	
	// register an entity menu item
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'elggcampitaly_regsiter_entity_menuitem');
	
	// extend a view
	elgg_extend_view('object/blog', 'elggcampitaly/my_view');
	
	// register a notification
	elgg_register_notification_event('object', 'blog', ['feature']);
	elgg_register_plugin_hook_handler('get', 'subscriptions', 'elggcampitaly_get_blog_notification_subscribers');
	elgg_register_plugin_hook_handler('prepare', 'notification:feature:object:blog', 'elggcampitaly_feature_blog_notification');
	
	// register an action
	elgg_register_action('elggcampitaly/toggle_feature', dirname(__FILE__) . '/actions/toggle_feature.php', 'admin');
}

/**
 * Add a menu item to the entity menu
 *
 * @param string         $hook         the name of the hook
 * @param string         $type         the type of the hook
 * @param ElggMenuItem[] $return_value current return value
 * @param array          $params       supplied params
 *
 * @return void|ElggMenuItem[]
 */
function elggcampitaly_regsiter_entity_menuitem($hook, $type, $return_value, $params) {
	
	$entity = elgg_extract('entity', $params);
	if (!($entity instanceof ElggBlog)) {
		return;
	}
	
	if (!elgg_is_admin_logged_in()) {
		return;
	}
	
	if ($entity->elggcampitaly_featured) {
		$return_value[] = ElggMenuItem::factory([
			'name' => 'elggcampitaly_unfeature',
			'text' => elgg_echo('elggcampitaly:menu:entity:unfeature'),
			'href' => "action/elggcampitaly/toggle_feature?guid={$entity->getGUID()}",
			'is_action' => true,
		]);
	} else {
		$return_value[] = ElggMenuItem::factory([
			'name' => 'elggcampitaly_feature',
			'text' => elgg_echo('elggcampitaly:menu:entity:feature'),
			'href' => "action/elggcampitaly/toggle_feature?guid={$entity->getGUID()}",
			'is_action' => true,
		]);
	}
	
	return $return_value;
}

/**
 * Add the owner of a blog to the subscribers
 *
 * @param string $hook         the name of the hook
 * @param string $type         the type of the hook
 * @param array  $return_value current return value
 * @param array  $params       supplied params
 *
 * @return void|array
 */
function elggcampitaly_get_blog_notification_subscribers($hook, $type, $return_value, $params) {
	
	$event = elgg_extract('event', $params);
	if (!($event instanceof \Elgg\Notifications\Event)) {
		return;
	}
	
	$entity = $event->getObject();
	if (!($entity instanceof ElggBlog)) {
		return;
	}
	
	// send an e-mail to the owner of the blog
	$return_value[$entity->getOwnerGUID()] = ['email'];
	
	return $return_value;
}

/**
 * Prepare the message for the notification
 *
 * @param string                           $hook         the name of the hook
 * @param string                           $type         the type of the hook
 * @param \Elgg\Notifications\Notification $return_value current return value
 * @param array                            $params       supplied params
 *
 * @return void|\Elgg\Notifications\Notification
 */
function elggcampitaly_feature_blog_notification($hook, $type, $return_value, $params) {
	
	$event = elgg_extract('event', $params);
	if (!($event instanceof \Elgg\Notifications\Event)) {
		return;
	}
	
	$entity = $event->getObject();
	if (!($entity instanceof ElggBlog)) {
		return;
	}
	
	$recipient = elgg_extract('recipient', $params);
	$language = elgg_extract('language', $params);
	
	$return_value->subject = elgg_echo('elggcampitaly:notification:feature:subject', [$entity->title], $language);
	$return_value->summary = elgg_echo('elggcampitaly:notification:feature:summary', [$entity->title], $language);
	$return_value->body = elgg_echo('elggcampitaly:notification:feature:body', [
		$recipient->name,
		$entity->title,
		$entity->getURL(),
	], $language);
	
	return $return_value;
}
