<?php
/**
 * CarteBlanche - PHP framework package - Tools
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Tool;

use \CarteBlanche\Abstracts\AbstractTool;

class TextToolBox extends AbstractTool
{

	var $view='text_tool_box.htm';

	var $content_id;
	var $font_size_tools=false;

	public function buildViewParams()
	{
		return array(
			'content_id'=>$this->content_id,
			'font_size_tools'=>$this->font_size_tools
		);
	}

}

// Endfile