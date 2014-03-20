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

namespace Tool\Exporter;

use \CarteBlanche\CarteBlanche;

abstract class AbstractExporterFormat
{

	var $dataCollection;
	var $dataFields;
	var $file_name;
	var $file_extension='txt';

	protected $exporter;
	protected $exported_file;

	/**
	 * Constructor : distribution of the formater options (must be some defined object properties)
	 * @param array $opts The object options
	 */
	public function __construct($opts = null, \Tool\Exporter $exporter)
	{
		$this->exporter = $exporter;
		if (!empty($opts))
		foreach($opts as $_opt_var=>$_opt_val) {
			if (property_exists($this, $_opt_var))
				$this->{$_opt_var} = $_opt_val;
		}
	}

	/**
	 * The date and hour of the day is automatically added to the filename with the final extension
	 */
	public function getExportedFileName()
	{
		if (empty($this->exported_file)) {
			$this->exported_file = CarteBlanche::getFullPath('web_tmp_dir')
				.(!empty($this->file_name) ? $this->file_name : 'export_'.get_class($this) )
				.'_'.date('dmy_His').'.'.$this->file_extension;
		}
		return $this->exported_file;
	}

	/**
	 * The real work of exporting, must be defined in each ExporterFormat class
	 */
	abstract function export();

}

// Endfile