<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Loader;

use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;
use PHPSu\Configuration\RawConfiguration\RawConsoleDto;
use PHPSu\Configuration\RawConfiguration\RawDatabaseBag;
use PHPSu\Configuration\RawConfiguration\RawDatabaseDto;
use PHPSu\Configuration\RawConfiguration\RawFilesystemBag;
use PHPSu\Configuration\RawConfiguration\RawFilesystemDto;
use PHPSu\Configuration\RawConfiguration\RawHostBag;
use PHPSu\Configuration\RawConfiguration\RawHostDto;
use PHPSu\Configuration\RawConfiguration\RawOptionBag;

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
            switch ($element->getName()) {
                case 'config':
                    $filesystemBag = new RawFilesystemBag(...$this->parseAllFilesystemsOf($element));
                    $databaseBag = new RawDatabaseBag(...$this->parseAllDatabasesOf($element));
                    break;
                case 'hosts':
                    $hostBag = new RawHostBag(...$this->parseHosts($element));
                    break;
                default:
                    throw new \Exception('element with Name ' . $element->getName() . ' not allowed');
            }
        }
        return new RawConfigurationDto($hostBag ?? null, $filesystemBag ?? null, $databaseBag ?? null);
    }

    protected function parseAllFilesystemsOf(\SimpleXMLElement $parentElement): \Generator
    {
        foreach ($parentElement->children() as $element) {
            if ($element->getName() === 'filesystem') {
                $optionBag = new RawOptionBag($this->parseOptionsOf($element));
                yield new RawFilesystemDto((string)$element['name'], (string)$element['type'], $optionBag);
            }
        }
    }

    protected function parseAllDatabasesOf(\SimpleXMLElement $parentElement): \Generator
    {
        foreach ($parentElement->children() as $element) {
            if ($element->getName() === 'database') {
                $optionBag = new RawOptionBag($this->parseOptionsOf($element));
                yield new RawDatabaseDto((string)$element['name'], (string)$element['type'], $optionBag);
            }
        }
    }

    protected function parseHosts(\SimpleXMLElement $parentElement): \Generator
    {
        foreach ($parentElement->children() as $element) {
            switch ($element->getName()) {
                case 'host':
                    $console = new RawConsoleDto(...$this->parseConsoleOf($element));
                    $filesystemBag = new RawFilesystemBag(...$this->parseAllFilesystemsOf($element));
                    $databaseBag = new RawDatabaseBag(...$this->parseAllDatabasesOf($element));
                    yield new RawHostDto((string)$element['name'], $console, $filesystemBag, $databaseBag);
                    break;
                default:
                    throw new \Exception('element with Name "' . $element->getName() . '" not allowed');
            }
        }
    }

    protected function parseConsoleOf(\SimpleXMLElement $parentElement): array
    {
        $console = null;
        foreach ($parentElement->children() as $element) {
            switch ($element->getName()) {
                case 'console':
                    if ($console !== null) {
                        throw new \Exception('Only one Console is allowed per Host');
                    }
                    $optionBag = new RawOptionBag($this->parseOptionsOf($element));
                    $console = [(string)$element['name'], (string)$element['type'], $optionBag];
                    break;
            }
        }
        return $console ?? [];
    }

    protected function parseOptionsOf(\SimpleXMLElement $parentElement): array
    {
        $result = [];
        foreach ($parentElement->children() as $element) {
            switch ($element->getName()) {
                case 'option':
                    $result[(string)$element['name']] = (string)$element['value'];
                    break;
                default:
                    throw new \Exception('element with Name "' . $element->getName() . '" not allowed');
            }
        }
        return $result;
    }
}
