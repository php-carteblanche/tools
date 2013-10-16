<?php
/**
 * CarteBlanche - PHP framework package - Tools
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Tool\Exporter\ExporterFormat;

use \Tool\Exporter\ExporterFormatInterface;

class ZIP extends ExporterFormatInterface
{

	var $file_extension='zip';
	var $clean_source = false;

	var $dir_name;
	
	protected $pclzip;
	protected static $pclzip_inited;

	public function export()
	{
		self::init();


		if (!empty($this->exporter->dataCollection) && is_array($this->exporter->dataCollection))
		{
		    foreach($this->exporter->dataCollection as $file) {
    			$this->addFile($file);
		    }
		}
        $this->pclzip->close();
		if (true===$this->clean_source)
			$this->cleanSource();
		
		return true;
/*
		$this->pclzip = new \PclZip( $this->getExportedFileName() );
		if (!empty($this->exporter->dataCollection) && is_array($this->exporter->dataCollection))
		{
			$this->pclzip->add($this->exporter->dataCollection, PCLZIP_OPT_REMOVE_ALL_PATH);
		}
		if (true===$this->clean_source)
			$this->cleanSource();

		return $this->checkErrors();
*/
	}

    protected function addFile($file_path)
    {
		self::init();
        if (true!==$this->pclzip->addFile($file_path, basename($file_path))) {
            throw new \Exception(
                sprintf('Can\'t add file "%s" to zip archive "%s"!', $file_path, $this->getExportedFileName())
            );
        }
    }

	public function init()
	{
		if (self::$pclzip_inited===true) return;
		if (!\CarteBlanche\App\Loader::classExists('\ZipArchive'))
			throw new \Exception('\ZipArchive class not found!');
        $this->pclzip = new \ZipArchive;
        if (true!==$this->pclzip->open($this->getExportedFileName(), \ZipArchive::CREATE)) {
            throw new \Exception(
                sprintf('Can\'t create zip archive "%s"!', $this->getExportedFileName())
            );
        }
/*
		@require_once 'vendor/pclzip-2-8-2/pclzip.lib.php';
		if (!\CarteBlanche\App\Loader::classExists('\PclZip'))
			throw new \Exception('PclZip class not found!');
*/
		self::$pclzip_inited = true;
	}

	public function checkErrors()
	{
		if (0!==$this->pclzip->errorCode())
		{
			throw new \RuntimeException( $this->pclzip->errorInfo(true) );
		}
		return true;
	}

	public function cleanSource()
	{
		if (!empty($this->exporter->dataCollection) && is_array($this->exporter->dataCollection))
		{
			foreach ($this->exporter->dataCollection as $_file)
			{
				@unlink($_file);
			}
		}
		if (!empty($this->dir_name))
			@rmdir($this->dir_name);
		return true;
	}

}

// Endfile