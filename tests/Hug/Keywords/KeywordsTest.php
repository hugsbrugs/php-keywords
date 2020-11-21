<?php

# For PHP7
// declare(strict_types=1);

// namespace Hug\Tests\Keywords;

use PHPUnit\Framework\TestCase;

use Hug\Keywords\Keywords as Keywords;

/**
 *
 */
final class KeywordsTest extends TestCase
{
    public $html_definition_naturopathie;
    public $text_definition_naturopathie;
    public $keywords_definition_naturopathie;

    function setUp(): void
    {
        $data = __DIR__ . '/../../../data/';
        
        $filename = $data . 'definition-naturopathie.html'; 
        $this->html_definition_naturopathie = file_get_contents($filename);

        $filename = $data . 'definition-naturopathie.txt'; 
        $this->text_definition_naturopathie = file_get_contents($filename);

        $filename = $data . 'keywords-definition-naturopathie.json'; 
        $this->keywords_definition_naturopathie = file_get_contents($filename);

    }

    /* ************************************************* */
    /* ********* Keywords::get_text_from_html ********** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanGetTextFromHtml()
    {
        
        $text = Keywords::get_text_from_html($this->html_definition_naturopathie);
        $this->assertIsString($text);
    }
    
    /* ************************************************* */
    /* ********** Keywords->str_word_count_utf8 ******** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanStrWordCountUtf8()
    {
        $Keywords = new Keywords($this->text_definition_naturopathie, null);
    	$words = $Keywords->str_word_count_utf8($this->text_definition_naturopathie);
        $this->assertEquals(2079, $words);
    }

    /* ************************************************* */
    /* *************** Keywords->Keywords ************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanKeywords()
    {
        $Keywords = new Keywords($this->text_definition_naturopathie, 'fr');
        $keywords = $Keywords->keywords;
        $this->assertIsArray($keywords);
        // $this->assertJsonStringEqualsJsonString(
        //     json_encode($keywords), 
        //     $this->keywords_definition_naturopathie
        // );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../../data/keywords-definition-naturopathie.json',
            json_encode($keywords)
        );
    }

}