<?php
	
/**
 * Export Gmail filters XML file to JSON and text file. 
 * 
 */

$doc = export([
	'src_xml' => 'mailFilters.xml',
	'save_json' => true
]);

text_export([
	'doc' => $doc,		
	'each_value' => function($name, $value) {
		return sprintf( "%s: %s\n", $name, $value );
	},
	'each_property' => function($name, $value) {
		return sprintf( "%s: %s\n", $name, $value );
	}
]);

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

/**
 * Export XML file to JSON.
 * 
 * @param string $config[filename] The XML file exported from Gmail (EX: mailFilters.xml).
 * @param string|bool $config[json_filename] (optional) If true save to mailFilters.json. If string use as filename.
 * 
 * @return object The JSON object representation of the XML file.
 */
function export($config) {

	$filename = $config['src_xml'] ?? die('Path to valid XML file required.');
	$json_filename = $config['save_json'] ?? null;
	$json_filename = $json_filename === true ? 'mailFilters.json' : ( is_string( $json_filename ) ? $json_filename : null );

	$xml = simplexml_load_file( $filename );
	$doc = [
		'feed' => [
			'title' => (string)$xml->title,
			'id' => (string)$xml->id,
			'updated' => (string)$xml->updated,
			'author' => [
				'name' => (string)$xml->author->name,
				'email' => (string)$xml->author->email,
			],
			'entries' => []
		]		
	];
	$bools = [
		'shouldStar',
		'shouldNeverSpam',
		'shouldAlwaysMarkAsImportant',
		'shouldArchive',
	];	
	$output = '';
	$entries = [];

	foreach ($xml->entry as $key => $entry) {
		$properties = $entry->children('apps', true)->property;
		$items = [];
		
		foreach ($properties as $key2 => $prop) {
			$atts = $prop->attributes();
			$name = (string)($atts->name ?? '');
			$value = (string)($atts->value ?? '');
			if( in_array( $name, $bools ) ) {
				$value = $value === 'true';
			}
			$items[$name] = $value;
		}
		
		$entries[] = [
			'category' => (string)($entry->category['term'] ?? ''),
			'title' => (string)($entry->title ?? ''),
			'id' => (string)($entry->id ?? ''),
			'updated' => (string)($entry->updated ?? ''),
			'content' => (string)($entry->content ?? ''),
			'properties' => $items
		];
	}

	$doc['feed']['entries'] = $entries;

	$json = json_encode( $doc, JSON_PRETTY_PRINT );
	if( $json_filename ) {
		file_put_contents( $json_filename, $json );
	}	
	$doc = json_decode( $json );
	return $doc;

}

function text_export($config) {

	$output = '';
	$filename = $config['filename'] ?? 'gmail_filters.txt';
	$each_value = $config['each_value'];
	$each_property = $config['each_property'];
	$before_each_entry = $config['before_each_entry'] ?? false;
	$after_each_entry = $config['after_each_entry'] ?? false;

	foreach( $config['doc']->feed->entries as $key => $entry ) {
		$output .= ( $before_each_entry ? $before_each_entry( $entry ) : '' );
		foreach ($entry as $entry_key => $entry_value) {
			if( $entry_key === 'properties' ) {
				foreach( $entry_value as $name => $value ) {
					$output .= $each_property( $name, $value );
				}
			}else{
				$output .= $each_value( $entry_key, $entry_value );
			}
		}
		$output .= ( $after_each_entry ? $after_each_entry( $entry ) : PHP_EOL );
	}

	file_put_contents( $filename, $output );

}
