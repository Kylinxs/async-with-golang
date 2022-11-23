
<?php
// File header

include('lang/en/language.php'); // Needed for providing a sensible default text for untranslated strings with context like : "edit_C(verb)"
$lang_current = array(
// "First string" => "First string",
// "Second string" => "Second string",
// "etc" => "etc",
);
$lang = array_replace($lang, $lang_current);