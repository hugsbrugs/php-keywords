<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Keywords\Keywords as Keywords;


$urls = [
    // 'https://naturo-paca.fr/',
    'https://naturo-paca.fr/definition-naturopathie',
    // 'https://naturo-paca.fr/qui-va-chez-un-naturopathe'
];
$lang = 'fr';

$htmls = [];
$keywords = [];

foreach ($urls as $key => $url)
{
	$html = request($url);
	// error_log($html);
	$htmls[$key] = $html;

	$text = Keywords::get_text_from_html($html);
	error_log($text);

	$Keywords = new Keywords($text, $lang);
	$keywords[$key] = $Keywords->keywords;
}

file_put_contents(__DIR__ . '/../data/keywords.json', json_encode($keywords, JSON_PRETTY_PRINT));
// file_put_contents(__DIR__ . '/../data/keywords.json', json_encode($keywords));
// error_log(print_r($keywords, true));


function request($url)
{
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:83.0) Gecko/20100101 Firefox/83.0");
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($ch);
	curl_close($ch);
	return $html;
}