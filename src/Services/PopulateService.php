<?php

namespace Pixelpoems\Search\Services;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Page;

class PopulateService extends Controller
{
    use Injectable;

    /**
     * @throws ReflectionException
     */
    public function populate(string $fileName = '', string $locale = null)
    {
        $pageIndexFileName = $this->populatePageData($fileName, $locale);
        $count = 0;
        $this->extend('populateAdditionalData', $pageIndexFileName, $locale, $count);
        $this->log('Additional Data populated: ' . $count  . "\n");

        if(SearchConfig::isElementalEnabled()) {
            $this->populateElementData($fileName, $locale);
        }
    }

    private function populatePageData(string $fileName = '', $locale = null)
    {
        $data = $this->getData(Page::class, $locale);

        if(!$fileName) $fileName = SearchService::getIndexFile('index');
        else $fileName = SearchService::getIndexFile($fileName);
        $this->log('Data Entities (Pages): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        $this->log($fileName . "\n");

        return $fileName;
    }

    /**
     * @throws ReflectionException
     */
    private function populateElementData(string $fileName = '', $locale = null)
    {
        $this->log('ELEMENTS:');

        $data = [];
        $excluded_elements = SearchConfig::getExcludedElements();
        $availableElementClasses = ClassInfo::subclassesFor(BaseElement::class);

        foreach ($availableElementClasses as $class) {
            if($class !== BaseElement::class) {
                if (!in_array($class, $excluded_elements ?? [])) {
                    $this->log($class);
                    $data = array_merge($data, $this->getData($class, $locale));
                }
            }
        }

        if(!$fileName) $fileName = SearchService::getIndexFile('index-elemental');
        else $fileName = SearchService::getIndexFile($fileName . '-elemental');
        $this->log('Data Entities (Elements): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        $this->log($fileName . "\n");
    }

    public function getData($class, $locale = null)
    {
        $objects = Versioned::get_by_stage($class, 'Live');

        if($class === Page::class) {
            $objects = $objects->filter(['ShowInSearch' => true]);
        }

        $data = [];
        foreach($objects as $object) {
            if(SearchConfig::isFluentEnabled()) {

                if($object->getExtensionInstance(FluentVersionedExtension::class)) {
                    if($object->isPublishedInLocale($locale)) {
                        $data[] = $this->getSearchIndexOfDataObject($class, $object->ID);
                    }
                } else if ($object->getExtensionInstance(FluentExtension::class)) {
                    if($object->isPublished($locale)) {
                        $data[] = $this->getSearchIndexOfDataObject($class, $object->ID);
                    }
                } else {
                    $data[] = $this->getSearchIndexOfDataObject($class, $object->ID);
                }

            } else {
                $data[] = $this->getSearchIndexOfDataObject($class, $object->ID);
            }
        }
        return $data;
    }

    private function getSearchIndexOfDataObject($class, $objectID)
    {
        $object = DataObject::get_by_id($class, $objectID);
        if(!$object) return;
        return $object->getSearchIndexData();
    }

    public function writeSearchFile($data, string $fileName)
    {
        // Check if folder exists
        if(!is_dir(SearchService::getIndexPath())) {
            mkdir(SearchService::getIndexPath(), 0777, true);
        }

        // Check if file exists and clean content
        if(file_exists($fileName)) file_put_contents($fileName, '');

        $file = fopen($fileName, 'w');
        fwrite($file, json_encode($data));
        fclose($file);
    }

    public function log($msg)
    {
        echo $msg . "\n";
    }
}
