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

class SearchBox extends AbstractTool
{

	var $view='search_box.htm';

	var $search_str;
	var $submit_url;
	var $advanced_search=false;
	var $hiddens=array();

	public function buildViewParams()
	{
		if (empty($this->submit_url)) 
			$this->submit_url = \Library\Helper\Url::getRequestUrl(true);

		return array(
			'search_str'=>$this->search_str,
			'submit_url'=>$this->submit_url,
			'hiddens'=>$this->hiddens,
			'advanced_search'=>$this->advanced_search,
		);
	}

}

// Endfile