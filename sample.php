<?php
	
require( 'export.php' );

$doc = export([
	/**
	 * Required path to xml file.
	 */
	'src_xml' => 'mailFilters.xml',
	/**
	 * Optional path to JSON file. 
	 * true: Save to mailFilters.json
	 * string: Use as path
	 * empty: Do not save to file - Default
	 */
	'save_json' => true 
]);

// Default - separate entries by newline.
text_export([
	'doc' => $doc,		
	'each_value' => function($name, $value) {
		return sprintf( "%s: %s\n", $name, $value );
	},
	'each_property' => function($name, $value) {
		return sprintf( "%s: %s\n", $name, $value );
	}
]);

// Add content before and after each entry.
text_export([
	'doc' => $doc,
	'filename' => 'gmail_filters_custom.txt',
	'each_value' => function($name, $value) {
		return sprintf( "%s: %s\n", $name, $value );
	},
	'each_property' => function($name, $value) {
		return sprintf( "%s: %s\n", $name, $value );
	},
	'before_each_entry' => function($entry) {
		return "-------- {$entry->id} ---\n";
	},
	'after_each_entry' => function($entry) {
		return "---\n\n";
	}
]);

// Tab delimited csv.
text_export([
	'doc' => $doc,
	'filename' => 'gmail_filters_csv.txt',
	'each_value' => function($name, $value) {
		return sprintf( "%s\t", $value );
	},
	'each_property' => function($name, $value) {
		return sprintf( "%s\t", $value );
	},
	'after_each_entry' => function($entry) {
		return PHP_EOL;
	}
]);