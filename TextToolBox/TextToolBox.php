<?php
/**
 * This file is part of the CarteBlanche PHP framework
 * (c) Pierre Cassat and contributors
 * 
 * Sources <http://github.com/php-carteblanche/tools>
 *
 * License Apache-2.0
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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