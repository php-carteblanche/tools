<?php
/**
 * CarteBlanche - PHP framework package - Tools
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Tool;

use \CarteBlanche\App\Kernel;
use \CarteBlanche\App\Router;
use \CarteBlanche\Abstracts\AbstractTool;

class DocumentField extends AbstractTool
{

	var $view='document.htm';

	var $document_client_name;
	var $document_path;
	var $document_content;
	var $document_url;
	var $max_width;
	var $max_height;
	var $display_image=true;
	var $html_content=true;

	public function buildViewParams()
	{
		if (!empty($this->document_content))
			$document = \CarteBlanche\Library\File::createFromContent( $this->document_content, $this->document_client_name );
		elseif (!empty($this->document_path))
			$document = new \CarteBlanche\Library\File( $this->document_path );
		else return array();

		return array(
			'document'=>$document,
			'document_url'=>!empty($this->document_url) ? $this->document_url : $document->getWebPath(),
			'max_width'=>$this->max_width,
			'max_height'=>$this->max_height,
			'display_image'=>$this->display_image,
			'html_content'=>$this->html_content
		);
	}

}

// Endfile