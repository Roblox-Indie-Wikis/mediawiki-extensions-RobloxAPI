<?php

$config = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$config['suppress_issue_types'] = array_merge( $config['suppress_issue_types'], [
	// we require php 8.1 implicitly by requiring MW 1.42
	'PhanCompatibleUnionType'
] );

return $config;
