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
use \CarteBlanche\App\Router;
use \Tool\Exporter\ExporterFormatInterface;

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