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

class Toggler extends AbstractTool
{

	// two different views
	var $view=null;
	var $views=array(
		'read' => 'toggler.htm',
		'edit' => 'toggler_form.htm',
	);

	var $value;
	var $name;
	var $table_name;
	var $object_id;
	var $db_name;
	var $controller = 'crud';
	
	var $_mode='read'; // read or edit

	public function buildViewParams()
	{
		self::getView();
		return array(
			'name'=>$this->name,
			'value'=>$this->value,
			'table_name'=>$this->table_name, 
			'object_id'=>$this->object_id, 
			'db_name'=>$this->db_name,
			'controller'=>$this->controller,
		);
	}

	public function getView()
	{
		if (!empty($this->_mode) && isset($this->views[$this->_mode]))
			$this->view = $this->views[$this->_mode];
	}

}

// Endfile