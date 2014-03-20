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