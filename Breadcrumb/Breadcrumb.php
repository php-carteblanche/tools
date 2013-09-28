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