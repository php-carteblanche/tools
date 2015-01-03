<?php
/**
 * This file is part of the CarteBlanche PHP framework.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * License Apache-2.0 <http://github.com/php-carteblanche/carteblanche/blob/master/LICENSE>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tool;

use \CarteBlanche\Abstracts\AbstractTool;

class Text extends AbstractTool
{

	var $view=null;

	var $original_str;
	var $final_str;

	var $max_length=null;
	var $readmore_link=' ...';

	var $markdown=false;

	var $strip_tags=false;
	var $allowed_tags='<strong><em><code>';

	public function buildViewParams()
	{
		if (empty($this->original_str)) return array('output'=>'');
		$str = $this->original_str;

		if ($this->markdown===true)
			$str = self::parseMarkdown( $str );

		if (!empty($this->strip_tags))
			$str = strip_tags( $str, $this->allowed_tags );

		if (!empty($this->max_length))
			$str = self::textCut( $str, $this->max_length, $this->readmore_link );

		$this->final_str = $str;

		return array(
			'output'=>$this->final_str,
		);
	}

	public static function parseMarkdown( $str='' )
	{
		if (empty($str)) return;

        // we now use our MarkdownExtended class
        $markdown = \MarkdownExtended\MarkdownExtended::create();
        return $markdown::transformString(html_entity_decode($str))
            ->getBody();

/*
        // we now use our Extended_Markdown class
        $markdown = new \Markdown\ExtraParser;
        return $markdown->transform( html_entity_decode($str) );

		require_once 'vendor/Extended_Markdown/markdown.php';
		return Markdown2Html( html_entity_decode($str) );

		require_once 'vendor/Markdown_Extra/markdown.php';
		return Markdown( html_entity_decode($str) );
*/
	}


    /**
     * Fonction qui tronque un texte en fonction d'une longueur specifiee, et lui ajoute ou non '...'
     * @param string $string La chaîne à couper
     * @param integer $length La longueur voulue, sans compter l'ajout final (par défaut 20)
     * @param string $end_str Chaîne finale à ajouter (par defaut '...')
     * @return string
     */
	public static function textCut($string=null, $length=20, $end_str=' ...')
	{
		if (is_null($string)) return;
		if (strlen($string) >= $length) {
			$stringint = substr($string, 0, $length);
			$last_space = strrpos($stringint, " ");
			$stringinter = substr($stringint, 0, $last_space).$end_str;
			if(strlen($stringinter) == strlen($end_str)) {
				$stringcut = $stringint.$end_str;
			} else {
				$stringcut = $stringinter;
			}
		} else {
			$stringcut = $string;
		}
		return $stringcut;
	}

}

// Endfile