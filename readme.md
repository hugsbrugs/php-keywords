# php-keywords

This library provides PHP functions to extract keywords from HTML and text. Read [PHP DOC](https://hugsbrugs.github.io/php-keywords)

[![Build Status](https://travis-ci.org/hugsbrugs/php-keywords.svg?branch=master)](https://travis-ci.org/hugsbrugs/php-keywords)
[![Coverage Status](https://coveralls.io/repos/github/hugsbrugs/php-keywords/badge.svg?branch=master)](https://coveralls.io/github/hugsbrugs/php-keywords?branch=master)

## Install

Install package with composer
```
composer require hugsbrugs/php-keywords
```
In your PHP code, load librairy
```php
require_once __DIR__ . '/vendor/autoload.php';
use Hug\Keywords\Keywords as Keywords;
```

## Usage

If you have HTML as input, first extract text from HTML (it also returns Title and meta description)
```php
$text = Keywords::get_text_from_html($html);
```

Supported languages codes are :
ar, bg, ca, cz, da, de, el, en, eo, es, et, fi, fr, hi, hr, hu, id, it, ka, lt, lv, nl, no, pl, pt, ro, ru, sk, sv, tr, uk, vi,

```php
$Keywords = new Keywords($text, $lang);
$kws = $Keywords->keywords;
```

If your language is not supported or if you want to use your own stop words, set 2nd argument as null and pass your own stop words array as 3rd argument. 
```php
$Keywords = new Keywords($text, null, ['my custom stop word array']);
$kws = $Keywords->keywords;
```

You can optionnaly pass a 4th argument as the max numbers of keywords to be returned. Set to 20 by default. Pass 0 if you want all keywords. In all cases it only returns keywords with occurence above 1.
```php
$Keywords = new Keywords($text, 'fr', [], 10);
$kws = $Keywords->keywords;
```

For the url https://naturo-paca.fr/definition-naturopathie, the library outputs :
```
[
    {
        "1": {
            "naturopathe": [
                12,
                "0.61"
            ],
            "m\u00e9decines": [
                11,
                "0.56"
            ],
            "naturopathie": [
                9,
                "0.46"
            ],
            "techniques": [
                9,
                "0.46"
            ],
            "m\u00e9decine": [
                9,
                "0.46"
            ],
            ...
        },
        "2": {
            "marie maugey": [
                5,
                "0.26"
            ],
            "maugey naturopathe": [
                4,
                "0.20"
            ],
            "\u2013 hippocrate": [
                3,
                "0.15"
            ],
            "m\u00e9decines alternatives": [
                2,
                "0.10"
            ],
            "m\u00e9decine conventionnelle": [
                2,
                "0.10"
            ],
            ...
        },
        "3": {
            "marie maugey naturopathe": [
                4,
                "0.20"
            ],
            "utilisation de techniques": [
                3,
                "0.15"
            ],
            "associe cette technique": [
                3,
                "0.15"
            ],
            "technique \u00e0 l\u2019\u00e9l\u00e9ment": [
                3,
                "0.15"
            ],
            "s'adresse la naturopathie": [
                2,
                "0.10"
            ],
            ...
        },
        "4": {
            "on associe cette technique": [
                3,
                "0.15"
            ],
            "associe cette technique \u00e0": [
                3,
                "0.15"
            ],
            "cette technique \u00e0 l\u2019\u00e9l\u00e9ment": [
                3,
                "0.15"
            ],
            "qui s'adresse la naturopathie": [
                2,
                "0.10"
            ],
            "la prise en charge": [
                2,
                "0.10"
            ],
            ...
        }
    }
]
```

## Unit Tests

https://github.com/php-coveralls/php-coveralls
```
vendor/phpunit/phpunit/phpunit --configuration phpunit.xml
```

## Author

Hugo Maugey [visit my website ;)](https://hugo.maugey.fr) 


### Online Tools
https://copywritely.com/keyword-density-checker/

### Dependecies
https://github.com/voku/stop-words
https://github.com/mtibben/html2text

### On same subject
https://github.com/sanketsharma411/keyword-density-analyzer/blob/flask-app/labs/4_selecting_nlp_libraries.ipynb
