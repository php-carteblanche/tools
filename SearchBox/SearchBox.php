<?php
/**
 * CarteBlanche - PHP framework package - Tools
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
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