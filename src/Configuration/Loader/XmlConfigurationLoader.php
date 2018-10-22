<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Loader;

use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;
use PHPSu\Configuration\RawConfiguration\RawDatabaseBag;
use PHPSu\Configuration\RawConfiguration\RawFilesystemBag;
use PHPSu\Configuration\RawConfiguration\RawHostBag;

class XmlConfigurationLoader extends AbstractConfigurationLoader
{
    public function __construct(array $options = [])
    {
        parent::__construct($options, ['file' => 'phpsu.xml']);
    }

    public function getRawConfiguration(): RawConfigurationDto
    {
        $configAsString = $this->getConfigFileContent();
        $xmlStructure = $this->xmlStringToXmlObject($configAsString);
        return $this->xmlStructureToConfigurationDto($xmlStructure);
    }

    protected function getConfigFileContent(): string
    {
        $file = $this->options['file'];
        return file_get_contents($file);
    }

    protected function xmlStringToXmlObject(string $configAsString): \SimpleXMLElement
    {
        return simplexml_load_string($configAsString, "SimpleXMLElement", LIBXML_NOCDATA);
    }

    protected function xmlStructureToConfigurationDto(\SimpleXMLElement $xmlStructure): RawConfigurationDto
    {
        foreach ($xmlStructure->children() as $element) {
        }
        $hostBag = new RawHostBag();
        $filesystemBag = new RawFilesystemBag();
        $databaseBag = new RawDatabaseBag();
        return new RawConfigurationDto($hostBag, $filesystemBag, $databaseBag);
    }
}
