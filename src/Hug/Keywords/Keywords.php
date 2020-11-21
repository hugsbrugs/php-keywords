<?php

namespace Hug\Keywords;

use voku\helper\StopWords as StopWords;
use Hug\Xpath\Xpath as Xpath;

/**
 *
 */
class Keywords
{
	public $text;
	public $lang;
	public $max_keywords;

	public $stop_words = [];
	public $text_word_count;
	public $text_words;

	public $keywords;

	function __construct($text, $lang = 'fr', $custom_stop_words = [], $max_keywords = 20)
	{
		$this->text = $text;
		$this->lang = $lang;
		$this->max_keywords = $max_keywords;

		try
		{
			if($this->lang!==null)
			{
				$SW = new StopWords();
				$this->stop_words = $SW->getStopWordsFromLanguage($this->lang);
			}
			else
			{
				$this->stop_words = $custom_stop_words;	
			}
		}
		catch(StopWordsLanguageNotExists $e)
		{
			error_log("Unsuported language code : " . $this->lang . ". Please provide your own stop words.");
		}

		# Clean Text

		// Lower case text
		$this->text = mb_strtolower($this->text, 'UTF-8');
		// Remove special chars
		$ban_chars = ['|','/','&',':',',',';','!','?','_','*',' -','- ','...'];
		$this->text = str_replace($ban_chars, '', $this->text);

		// Count words in text
		$this->text_word_count = count(preg_split('/\s+/', $this->text));
		// Split text into words
		$this->text_words = preg_split('/\s+/', $this->text);

		$this->sort_keywords();
	}

	/**
	 *
	 */
	public function sort_keywords()
	{
		$keywordsSorted0 = ''; // 1 word match 
		$keywordsSorted1 = ''; // 2 word phrase match 
		$keywordsSorted2 = ''; // 3 word phrase match 
		$keywordsSorted3 = ''; // 4 word phrase match 

		$word0stop = false;
		$word1stop = false;
		$word2stop = false;
		$word0stop = false;
			
		for ($i = 0; $i < count($this->text_words); $i++)
		{
			// 1 word phrase match 
			if ($i+0 < $this->text_word_count)
			{
				$word0stop = in_array($this->text_words[$i], $this->stop_words);
				
				if(!$word0stop)
				{
					$keywordsSorted0 .= $this->text_words[$i].',';			
				}
			}

			// 2 word phrase match 
			if ($i+1 < $this->text_word_count)
			{
				$word1stop = in_array($this->text_words[$i+1], $this->stop_words);
				
				if(!($word0stop||$word1stop))
				{
					$keywordsSorted1 .= $this->text_words[$i].' '.$this->text_words[$i+1].',';
				}
			} 
			
			// 3 word phrase match 
			if ($i+2 < $this->text_word_count)
			{
				$word2stop = in_array($this->text_words[$i+2], $this->stop_words);
				
				if(count(array_filter([$word0stop,$word1stop,$word2stop]))<2)
				{
					$keywordsSorted2 .= $this->text_words[$i].' '.$this->text_words[$i+1].' '.$this->text_words[$i+2].',';			
				}
			} 
			
			// 4 word phrase match 
			if ($i+3 < $this->text_word_count)
			{
				$word3stop = in_array($this->text_words[$i+3], $this->stop_words);
				
				if(count(array_filter([$word0stop,$word1stop,$word2stop,$word3stop]))<3)
				{
					$keywordsSorted3 .= $this->text_words[$i].' '.$this->text_words[$i+1].' '.$this->text_words[$i+2].' '.$this->text_words[$i+3].',';
				}
			} 
		}

		for ($i = 0; $i <= 3; $i++)
		{
			// Build array form string. 
			${'keywordsSorted'.$i} = array_filter(explode(',', ${'keywordsSorted'.$i}));			
			${'keywordsSorted'.$i} = array_count_values(${'keywordsSorted'.$i});
			${'keywordsSorted'.$i} = array_filter(${'keywordsSorted'.$i}, function($n){ return $n > 1; });
			asort(${'keywordsSorted'.$i});
			arsort(${'keywordsSorted'.$i});
			
			foreach (${'keywordsSorted'.$i} as $key => $value)
			{
				${'keywordsSorted'.$i}[$key] = [
					$value, 
					number_format((100 / $this->text_word_count * $value),2)
				];
			}
		}

		if($this->max_keywords===0)
		{
			$this->keywords = [
				"1" => $keywordsSorted0,
				"2" => $keywordsSorted1,
				"3" => $keywordsSorted2,
				"4" => $keywordsSorted3
			];
		}
		else
		{
			$this->keywords = [
				"1" => array_slice($keywordsSorted0, 0, $this->max_keywords),
				"2" => array_slice($keywordsSorted1, 0, $this->max_keywords),
				"3" => array_slice($keywordsSorted2, 0, $this->max_keywords),
				"4" => array_slice($keywordsSorted3, 0, $this->max_keywords)
			];
		}

		return $this->keywords;
	}

	/**
	 *
	 */
	public function str_word_count_utf8($str)
	{
		return count(preg_split('~[^\p{L}\p{N}\']+~u', $str));
	}

	/**
	 *
	 */
	public static function get_text_from_html($html)
	{
		$text = null;
		try
		{
			$title = Xpath::extract_first($html, '//title');

			$meta_description = Xpath::extract_first($html, '//meta[@name="description"]/@content');

			// $file = tempnam(sys_get_temp_dir(), 'tags');
			// file_put_contents($file, $html);
			// $report['meta'] = get_meta_tags($file);
			// error_log('meta : ' . print_r($report['meta'], true));
			// unlink($file);

			$html = new \Html2Text\Html2Text($html, ['do_links' => 'none', 'width' => 0]);
			$text = $html->getText();

			// Remove Image Alt 
			$text = preg_replace('`\[[^\]]*\]`','',$text);

			// Remove multiple line breaks
			$text = preg_replace("/[\r\n]+/", "\n", $text);


			$text = $title . "\n" . $meta_description . "\n" . $text;			
		}
		catch(Exception $e)
		{
			error_log("Error while extracting text from HTML : " . $e);
		}
		return $text;
	}

}