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
		"version"		=> "1.0",
		"codename"		=> "customcodename",
		"compatibility" => "18*"
	);
}

$plugins->add_hook("parse_message_start", "parse_custom_tags");
$plugins->add_hook("parse_message_end", "fix_newlines");


function parse_custom_tags($message)
{
	global $custom_code_matches;
	$pattern = "#\[(code|php|python|output|error)\](.*?)\[/\\1\]|\[icode\]([^\n]*?)\[/icode\]#si";

	preg_match_all($pattern, $message, $custom_code_matches, PREG_SET_ORDER);
	$message = preg_replace($pattern, "<mybb-code>\n", $message);

	foreach ($custom_code_matches as $text) {
		$type = my_strtolower($text[1]);

		// let mybb handle code and php tags
		if (($type == "code") || ($type == "php")) {
			$message = preg_replace("#<mybb-code>\n#", $text[0], $message, 1);
		}

		else
		{
			if ($type == "") {
				$type = "icode";
				$content = $text[3];
			}
			else {
				$content = $text[2];
			}
			
			// escape brackets to avoid further parsing
			$content = str_replace(array("[", "]", "<", ">"), array("&#91;","&#93;", "&lt;", "&gt;"), $content);
			// horrible hack to avoid nl2br being executed on our code blocks
			$content = str_replace("\n", "<newline>", $content);

			$message = preg_replace("#<mybb-code>\n#", format_code($type, $content), $message, 1);
		}
	}
	return $message;
}


function format_code($type, $content)
{
	if ($type == "python") {
		return "<pre class=\"brush: python\" title=\"Python Code:\">".$content."</pre>";
	}

	if ($type == "icode") {
		$code_tag = "<code class=\"icode\">";
	}
	else {
		$code_tag = "<code class=\"codeblock ".$type."\"><div class=\"title\">".ucfirst($type).":</div>";
	}


	return $code_tag.$content."</code>";

}


function fix_newlines($message) {
	// get our newlines back
	return str_replace("<newline>", "\n", $message);
}
