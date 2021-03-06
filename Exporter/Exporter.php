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
use \CarteBlanche\App\Router;
use \Tool\Exporter\AbstractExporterFormat;

class Exporter extends AbstractTool
{

	var $view='';
	var $format=null;

	var $dataCollection;
	var $dataFields;

	var $exporter_format;
	var $formater_options=array();
	
	private static $formater_mask = '\Tool\Exporter\ExporterFormat\%s';

	/**
	 * The constructor : launch parent constructor and creates chosen ExporterFormat
	 * @param array $opts An array of the tool options
	 */
	public function __construct($opts = array())
	{
		parent::__construct( $opts );
		if (!empty($this->format)) {
			$_cls = sprintf( self::$formater_mask, $this->format );
			if (class_exists($_cls)) {
				$this->exporter_format = new $_cls( $this->formater_options, $this );
			} else {
				throw new InvalidArgumentException("Trying to export in an unknown format !");
			}
		} else {
			throw new InvalidArgumentException("Trying to export with no format !");
		}
	}

	/**
	 * The final export
	 */
	public function export()
	{
		return $this->exporter_format->export();
	}
	
	/**
	 * Get the filename of the exported file
	 */
	public function getExportedFileName()
	{
		return $this->exporter_format->getExportedFileName();
	}
	
	/**
	 * The final rendering of the tool
	 */
	public function render()
	{
		return $this->export();
	}
	
	public function buildViewParams()
	{
		return array();
	}

}

// Endfile