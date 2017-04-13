<?php

$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}
