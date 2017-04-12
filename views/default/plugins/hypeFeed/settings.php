<?php

$entity = elgg_extract('entity', $vars);

$tabs = \hypeJunction\Feed\Config::getTabs();

$options = [
	0 => elgg_echo('feed:tabs:disabled'),
];

for($i = 1; $i <= count($tabs); $i++) {
	$options[$i] = elgg_echo('feed:tabs:position', [$i]);
}

foreach ($tabs as $item) {
	$tab = $item->getName();
	$setting_name = "tab:$tab";
	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('feed:tabs:tab', [$item->getText()]),
		'name' => "params[$setting_name]",
		'value' => $item->getPriority(),
		'options_values' => $options,
	]);
}

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('feed:aggregation_interval'),
	'#help' => elgg_echo('feed:aggregation_interval:help'),
	'name' => 'params[aggregation_interval]',
	'value' => $entity->aggregation_interval ? : 'day',
	'options_values' => [
		'hour' => elgg_echo('feed:aggregation_interval:hour'),
		'three_hours' => elgg_echo('feed:aggregation_interval:three_hours'),
		'six_hours' => elgg_echo('feed:aggregation_interval:six_hours'),
		'twelve_hours' => elgg_echo('feed:aggregation_interval:twelve_hours'),
		'day' => elgg_echo('feed:aggregation_interval:day'),
		'week' => elgg_echo('feed:aggregation_interval:week'),
		'month' => elgg_echo('feed:aggregation_interval:month'),
	],
]);
