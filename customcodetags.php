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


function parse_custom_tags($message)
{
	$pattern = "#\[(code|php|python|output|error)\](.*?)\[/\\1\]|\[icode\]([^\n]*?)\[/icode\]#si";

	preg_match_all($pattern, $message, $code_matches, PREG_SET_ORDER);
	$message = preg_replace($pattern, "<mybb-code>\n", $message);

	foreach ($code_matches as $text) {
		$type = my_strtolower($text[1]);

		// let mybb handle code and php tags
		if (($type == "code") || ($type == "php")) {
			$message = preg_replace("#<mybb-code>\n#", $text[0], $message, 1);
		}

		else
		{
			if ($type == "") { // icode tag
				$type = "icode";
				$content = $text[3];
			}
			else {
				$content = $text[2];
			}

			// escape brackets to avoid further parsing
			// replace newlines with <br> tags, so nl2br() doesn't mess with spacing in our <pre> tags
			$content = str_replace(array("[", "]", "<", ">", "\n"), array("&#91;","&#93;", "&lt;", "&gt;", "<br />"), $content);

			if ($type == "python") {
				$message = preg_replace("#<mybb-code>\n#", "<pre class=\"brush: python\">".$content."</pre>", $message, 1);
			}
			else {
				$message = preg_replace("#<mybb-code>\n#", "<code class=\"".$type."\">".$content."</code>", $message, 1);
			}
		}
	}
	return $message;
}