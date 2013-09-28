<?php
/**
 * CarteBlanche - PHP framework package - Tools
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace Tool\Exporter\ExporterFormat;

use \Tool\Exporter\ExporterFormatInterface;

class CSV extends ExporterFormatInterface
{

	var $file_extension='csv';
	var $delimiter=',';
	var $enclosure='"';

	public function export()
	{
		self::organize();
		$_f = $this->getExportedFileName();
		if (false!==$fp = @fopen($_f, 'w')) {
			if (!empty($this->exporter->dataFields) && is_array($this->exporter->dataFields)) {
				fputcsv($fp, $this->exporter->dataFields, $this->delimiter, $this->enclosure);
			}
			if (!empty($this->exporter->dataCollection) && is_array($this->exporter->dataCollection)) {
				foreach ($this->exporter->dataCollection as $fields)  {
					fputcsv($fp, $fields, $this->delimiter, $this->enclosure);
				}
			}
			return fclose($fp);
		}
		return false;
	}

	public function organize()
	{
		$values=array();
		foreach($this->exporter->dataCollection as $entry) {
			if (!empty($this->exporter->dataFields) && is_array($this->exporter->dataFields)) {
				$entry_set = array();
				foreach ($this->exporter->dataFields as $field)  {
					$entry_set[] = $entry[$field];
				}
			} else {
				$entry_set = $entry;
			}
			$index = isset($entry['id']) ? $entry['id'] : null;
			if (!is_null($index))
				$values[$index] = $entry_set;
			else
				$values[] = $entry_set;
		}
		ksort($values);
		$this->exporter->dataCollection = $values;
	}

}

// Endfile