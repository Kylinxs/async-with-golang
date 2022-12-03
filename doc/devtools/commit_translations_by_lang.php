
<?php

# (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
#
# All Rights Reserved. See copyright.txt for details and a complete list of authors.
# Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
# $Id: commit_translations_by_lang.php  2019-2-22 12:28 PM Axel Mwenze $

//die("REMOVE THIS LINE TO USE THE SCRIPT.\n");

//if (! isset($argv[1])) {
//    echo "\nUsage: php export_all_translations_to_file.php\n";
//    echo "Example: php export_translations_to_file.php\n";
//    die;
//}

require_once('tiki-setup.php');
require_once('lang/langmapping.php');
require_once('lib/language/Language.php');
require_once('lib/language/LanguageTranslations.php');
require_once('gittools.php');


$langlib = new LanguageTranslations();
$langguage = new Language();
$retour = $langlib->getAllDbTranslations();
$user_translations = [];

function rangeByLang($changes, $lang)
{
    $usernames = [];
    foreach ($changes['translations'] as $change) {
        if ($change['lang'] == $lang) {
            array_push($usernames, $change['user']);
        }
    }
    $usernames = array_unique($usernames, SORT_REGULAR);
    $specific_tring = join(",", $usernames);
    return $specific_tring;
}

$final_commit_list = [];

foreach ($retour['translations'] as $current_lang) {
    $usernames = rangeByLang($retour, $current_lang['lang']);
    array_push($final_commit_list, ['lang' => $current_lang['lang'], 'usernames' => $usernames]);
}
$final_commit_list = array_unique($final_commit_list, SORT_REGULAR);

foreach ($final_commit_list as $langToWrite) {
    try {
        $language = new LanguageTranslations($langToWrite['lang']);
        $stats = $language->writeLanguageFile(false, true);
    } catch (Exception $e) {
        die("{$e->getMessage()}\n");
    }
}

foreach ($final_commit_list as $trans) {
    $langmap = $langguage::get_language_map();
    $lang_found = $langmap[$trans['lang']];
    array_push($user_translations, ["user" => $trans['usernames'], "lang" => $lang_found, "langdir" => $trans['lang']]);
}
if (has_uncommited_changes("./lang")) {
    echo "there is uncommitted changes \n";
    $description_merge = [];
    $title_merge = "[TRA] Automatic Merge request of translations contributed to http://i18n.tiki.org";
    foreach ($user_translations as $all_translations) {
        if (empty($all_translations['user'])) {
            $all_translations['user'] = 'Anonymous';
        }
        $commit_description = "Automatic commit of $all_translations[lang] translation contributed by $all_translations[user] to http://i18n.tiki.org";
        $description_merge[] = $commit_description;
        $return_value = commit_specific_lang($all_translations['langdir'], $commit_description);
        echo "commit :  " . $return_value;
    }
    $description_merge = implode(" , ", $description_merge);
    push_create_merge_request($title_merge, $description_merge, "master");
} else {
    echo "There is no translation to commit";
}