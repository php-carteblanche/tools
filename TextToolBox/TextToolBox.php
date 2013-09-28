<?php
/**
 * CarteBlanche - PHP framework package - Tools
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace Tool;

use \CarteBlanche\App\Abstracts\AbstractTool;

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