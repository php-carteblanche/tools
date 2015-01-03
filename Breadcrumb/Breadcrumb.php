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