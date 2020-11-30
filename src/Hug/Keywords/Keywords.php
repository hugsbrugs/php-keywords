<?php

namespace Hug\Keywords;

use voku\helper\StopWords as StopWords;
use Hug\Xpath\Xpath as Xpath;
use LanguageDetection\Language;
use voku\helper\StopWordsLanguageNotExists;

/**
 *
 */
class Keywords
{
	public $text;
	public $lang;
	public $max_keywords;
	public $min_kwd_nb;

	public $stop_words = [];
	public $ban_chars = ['|','/','=','&','.',':',',',';','!','?','_','*',' -','- ','→','–','«','»','+','✔','#','¿','<','>','[',']','{','}','(',')'];
	public $text_word_count;
	public $text_words;

	public $keywords;

	/**
	 *
	 */
	function __construct($text, $lang = 'auto', $custom_stop_words = [], $max_keywords = 20, $ban_chars = [], $min_kwd_nb = 2)
	{
		$this->text = $text;
		$this->lang = $lang;
		$this->max_keywords = $max_keywords;
		$this->min_kwd_nb = $min_kwd_nb;

		if(count($ban_chars)>0)
			$this->ban_chars = $ban_chars;

    	if($this->lang==='auto')
    	{
            $ld = new Language;
            $result = $ld->detect($this->text)->bestResults()->close();
            $keys = array_keys($result);
            if(isset($keys[0]))
			{
	            $this->lang = $keys[0];
			}
			else
			{
				$this->lang = 'en';
			}
			# When language code is not 2 digit (pt-PT)
            if(strlen($this->lang)>2)
            {
            	$this->lang = substr($this->lang, 0, 2);
            }
            // error_log('lang : '.$this->lang);
        }

		try
		{
			if(count($custom_stop_words)>0)
			{
				$this->stop_words = $custom_stop_words;
			}
			else
			{
				$SW = new StopWords();
				$this->stop_words = $SW->getStopWordsFromLanguage($this->lang);
			}

			// Lower case all stop words
			$this->stop_words = array_map(function($n){ return mb_strtolower($n, 'UTF-8'); }, $this->stop_words);
		}
		catch(StopWordsLanguageNotExists $e)
		{
			error_log("Unsuported language code : " . $this->lang . ". Please provide your own stop words.");
		}

		# Clean Text

		// Lower case text
		$this->text = mb_strtolower($this->text, 'UTF-8');
		// Remove special chars
		$this->text = str_replace($this->ban_chars, '', $this->text);
		// Remove numbers
		$this->text = preg_replace('/\d+/u', '', $this->text);
		// Replace multiple spaces by single space
		// $this->text = preg_replace('!\s+!', ' ', $this->text);
		$this->text = preg_replace('/\s+/u', ' ', $this->text);

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
				$word0stop = in_array(trim($this->text_words[$i]), $this->stop_words);
				
				if(!$word0stop)
				{
					$keywordsSorted0 .= $this->text_words[$i].',';			
				}
			}

			// 2 word phrase match 
			if ($i+1 < $this->text_word_count)
			{
				$word1stop = in_array(trim($this->text_words[$i+1]), $this->stop_words);
				
				if(!($word0stop||$word1stop))
				{
					$keywordsSorted1 .= $this->text_words[$i].' '.$this->text_words[$i+1].',';
				}
			} 
			
			// 3 word phrase match 
			if ($i+2 < $this->text_word_count)
			{
				$word2stop = in_array(trim($this->text_words[$i+2]), $this->stop_words);
				
				if(count(array_filter([$word0stop,$word1stop,$word2stop]))<2)
				{
					$keywordsSorted2 .= $this->text_words[$i].' '.$this->text_words[$i+1].' '.$this->text_words[$i+2].',';			
				}
			} 
			
			// 4 word phrase match 
			if ($i+3 < $this->text_word_count)
			{
				$word3stop = in_array(trim($this->text_words[$i+3]), $this->stop_words);
				
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
			if($this->min_kwd_nb > 0)
			{
				${'keywordsSorted'.$i} = array_filter(${'keywordsSorted'.$i}, function($n){ return $n >= $this->min_kwd_nb; });
			}
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