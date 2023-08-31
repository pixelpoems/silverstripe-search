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
        $this->extend('populateAdditionalData', $pageIndexFileName, $locale);

        if(SearchConfig::isElementalEnabled()) {
            $this->populateElementData($fileName, $locale);
        }
    }

    private function populatePageData(string $fileName = '', $locale = null)
    {
        $data = $this->getData(Page::class, $locale);

        if(!$fileName) $fileName = SearchService::getIndexFile('index');
        else $fileName = SearchService::getIndexFile($fileName);
        self::log('Data Entities (Pages): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        self::log($fileName . "\n");

        return $fileName;
    }

    /**
     * @throws ReflectionException
     */
    private function populateElementData(string $fileName = '', $locale = null)
    {
        self::log('ELEMENTS:');

        $data = [];
        $excluded_elements = SearchConfig::getExcludedElements();
        $availableElementClasses = ClassInfo::subclassesFor(BaseElement::class);

        foreach ($availableElementClasses as $class) {
            if($class !== BaseElement::class) {
                if (!in_array($class, $excluded_elements ?? [])) {
                    self::log($class);
                    $data = array_merge($data, $this->getData($class, $locale));
                }
            }
        }

        if(!$fileName) $fileName = SearchService::getIndexFile('index-elemental');
        else $fileName = SearchService::getIndexFile($fileName . '-elemental');
        self::log('Data Entities (Elements): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        self::log($fileName . "\n");
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

    public static function log($msg)
    {
        echo $msg . "\n";
    }
}
