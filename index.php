<?php
// Including DStyles
require 'DStyles.php';

$styleID = (IsSet($_GET['style']) && !empty($_GET['style'])) ? (int) $_GET['style'] : 1;

if ($styleID < 1)
	$styleID = 1;
if ($styleID > 2)
	$styleID = 2;

// ---[ Setting up DStyles ]---
// Creating DStyles object
// Fixed path: folder1/a/b/s/my data/d/name/
$style = new DStyles('folder1/a\///b\\s/my data/d\\\///', 'name', 'tpl');

// Changing home directory
// Change path to: styles/name/
$style->setHome('styles');

// Changing style directory
// Change path to: styles/tpl1/
$style->setStyle("tpl$styleID");

// Changing default extension
$style->setExt('.html');

// ---[ Actual content ]---
/*
header($data) method is an equivalent to get('header')->set($data)

set() method can be used in following ways:
- set('CONSTANT', 'value')
- set(array(
'CONSTANT1'	=> 'value1',
'CONSTANT2'	=> 'value2',
...
))
However, header() and footer() methods accept only arrays as a parameter

Every method used after get() can by linked directly to another, for example:
$obj->get( ... )->registerTrigger( ... )->set( ... )->trigger( ... )

You can both saving template object to a variable (useful in loops) or display result directly.
*/
// Getting header
$header = $style->header();
$header->set(array(
	'TITLE'		=> "My site's title",
	'CHARSET'	=> 'utf-8'
));
$header->result();



/*
Here You can put Your site's "static" content
*/
?><p>
	Set style:
	<a href="./" >#1</a>
	<a href="./?style=2" >#2</a>
</p><?php

// Getting some content; example of displaying generated data directly
$style->get('index')->registerTrigger(array(
	// Register any trigger
	'JAVASCRIPT'
))->set(array(
	// Set content
	'CONTENT' => implode(array_map('chr', range(65, 90)))
))->trigger(array(
	// Triggers use logical values
	'JAVASCRIPT' => ($styleID == 1)
))->result();

// An example od splitting content
$table = $style->get('table')->split('SPLIT');
$table->result(0); // Get first part of split data

// Here we are in table content; example of using template file with non-default set extension
$element = $style->get('table_element', '.tpl');
for ($x = 97; $x <= 110; $x++) {
	// In every loop constants values are overridden by new ones
	$element->set(array(
		'ID'	=> $x,
		'CHAR'	=> chr($x)
	))->result();
}

$table->result(1); // Get second (and last in this example) part of split data



// Getting footer; same effect as: $footer = $style->footer(array('YEAR' => date('Y')))->create()
$footer = $style->get('footer')->set('YEAR', date('Y'))->create();
echo $footer;
?>