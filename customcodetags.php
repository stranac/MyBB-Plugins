<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}


function customcodetags_info()
{
	return array(
		"name"			=> "Custom Code Tags",
		"description"	=> "Adds custom MyCode for python code, output, errors and inline code",
		"author"		=> "stranac",
		"version"		=> "1.1",
		"codename"		=> "customcodename",
		"compatibility" => "18*"
	);
}

$plugins->add_hook("parse_message_start", "find_custom_tags");
$plugins->add_hook("parse_message_end", "replace_custom_tags");


function find_custom_tags($message)
{
	global $custom_code_matches, $parser, $mybb;

	// to quote class_parser.php: "Get rid of carriage returns for they are the workings of the devil"
	$message = str_replace("\r", "", $message);

	// if MyCode needs to be replaced, filter out all code tags.
	if(!empty($parser->options['allow_mycode'])) {
		$pattern = "#\[(code|php|python|output|error)\](.*?)\[/\\1\]\n*|\[icode\]([^\n]*?)\[/icode\]|`([^\n]*?)`#si";

		preg_match_all($pattern, $message, $custom_code_matches, PREG_SET_ORDER);
		$message = preg_replace($pattern, "---custom-code-tags-plugin---", $message);
	}

	return $message;
}


function replace_custom_tags($message) {
	global $custom_code_matches, $parser;

	if(!empty($parser->options['allow_mycode'])) {
		// if we split up any code tags, parse them and glue it all back together
		if(count($custom_code_matches) > 0) {

			foreach($custom_code_matches as $match) {
				$type = my_strtolower($match[1]);
				$content = my_strtolower($match[2]);

				if ($type == "") { // icode and ` matched by separate regex, so details need to be corrected
					$type = "icode";
					$content = ($match[3] != "") ? $match[3] : $match[4];
				}

				$content = $parser->parse_html($content);
				$code = format_code($type, $content);

				$message = preg_replace("#---custom-code-tags-plugin---\n?#", $code, $message, 1);
			}
		}
	}
	
	return $message;
}


function format_code($type, $content)
{
	// hijack [code] and [php] tags
	if (in_array($type, ["python", "code", "php"])) {
		return "<pre class=\"brush: python\" title=\"Python Code:\">".$content."</pre>";
	}
	// inline code
	elseif ($type == "icode") {
		return "<code class=\"icode\">".$content."</code>";
	}
	// [error] and [output] tags
	else {
		return "<pre><code class=\"codeblock ".$type."\"><div class=\"title\">".ucfirst($type).":</div>".$content."</code></pre>";
	}
}
