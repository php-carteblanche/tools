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

class Breadcrumb extends AbstractTool
{

	var $view='breadcrumb.htm';

	var $home;
	var $current;
	var $links;

	public function buildViewParams()
	{
		return array(
			'home'=>$this->home,
			'current'=>$this->current,
			'links'=>array_reverse($this->links),
		);
	}

}

// Endfile