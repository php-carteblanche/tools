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
use \CarteBlanche\App\Router;
use \CarteBlanche\CarteBlanche;

class Pager extends AbstractTool
{

	var $view='pager.htm';

	var $table_name;
	var $total;
	var $limit=5;
	var $offset=0;
	var $url_args=array();

	var $total_pages=null;
	var $current_page=null;
	var $first_page=null;
	var $last_page=null;
	
	var $items_select;
	var $pager_link_mask;
	var $limiter_link_mask;

	public function buildViewParams()
	{
        $router = CarteBlanche::getContainer()->get('router');
		self::calculate();
		return array(
			'table_name'=>$this->table_name,
			'total'=>$this->total,
			'limit'=>$this->limit,
			'offset'=>$this->offset,
			'total_pages'=>$this->total_pages,
			'current_page'=>$this->current_page,
			'first_page'=>$this->first_page,
			'last_page'=>$this->last_page,
			'pager_link_mask'=>isset($this->pager_link_mask) ? $this->pager_link_mask : 
				$router->buildUrl(array_merge($this->url_args, array(
					'offset'=>'%s','limit'=>$this->limit
				))),
			'limiter_link_mask'=>isset($this->limiter_link_mask) ? $this->limiter_link_mask : 
				$router->buildUrl(array_merge($this->url_args, array(
					'offset'=>$this->offset,'limit'=>'%s'
				))),
			'items_select'=>isset($this->items_select) ? $this->items_select : null,
		);
	}

	protected function calculate()
	{
		$this->total_pages = ceil( $this->total / $this->limit );

		$this->current_page = ceil( ($this->offset + 1) / $this->limit );
		if ($this->current_page < 1) 
			$this->current_page = 1;

		$this->first_page = $this->current_page - 2;
		if ($this->first_page < 1) 
			$this->first_page = 1;

		$this->last_page = $this->current_page + 2;
		if ($this->last_page > $this->total_pages) 
			$this->last_page = $this->total_pages;
	}

}

// Endfile