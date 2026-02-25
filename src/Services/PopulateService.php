<?php

namespace Pixelpoems\Search\Services;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
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
        [$pageIndexFileName, $pageData] = $this->populatePageData($fileName, $locale);
        $additionalData = [];
        $this->extend('populateAdditionalData', $pageIndexFileName, $locale, $additionalData);
        $this->log('Additional Data populated: ' . count($additionalData)  . "\n");
        $this->writeSearchFile(array_merge($pageData, $additionalData), $pageIndexFileName);
        $this->log($fileName . "\n");

        if(SearchConfig::isElementalEnabled()) {
            $this->populateElementData($fileName, $locale);
        }
    }

    private function populatePageData($fileName, $locale)
    {
        $data = $this->getData(Page::class, $locale);
        if ($fileName === '' || $fileName === '0') {
            $fileName = SearchService::getIndexFile('index');
        } else {
            $fileName = SearchService::getIndexFile($fileName);
        }

        $this->log('Data Entities (Pages): ' . count($data));

        return [$fileName, $data];
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

        // When a locale is set, collect all ElementalArea IDs that are actually
        // published for that locale. We query every *_Localised table that has an
        // ElementalAreaID column – this way the filter is generic and does not need
        // to know about specific page types. Elements whose ParentID is not in this
        // set belong to a different locale and are skipped.
        $filter = [];
        if ($locale && SearchConfig::isFluentEnabled()) {
            $validAreaIds = $this->getPublishedAreaIdsForLocale($locale);
            $this->log('Valid ElementalArea IDs for locale ' . $locale . ': ' . count($validAreaIds));
            // Pass an impossible match when the list is empty so no elements are indexed.
            $filter = ['ParentID' => $validAreaIds ?: [0]];
        }

        foreach ($availableElementClasses as $class) {
            if ($class !== BaseElement::class && !in_array($class, $excluded_elements ?? [])) {
                $this->log($class);
                $data = array_merge($data, $this->getData($class, $locale, $filter));
            }
        }
        if ($fileName === '' || $fileName === '0') {
            $fileName = SearchService::getIndexFile('index-elemental');
        } else {
            $fileName = SearchService::getIndexFile($fileName . '-elemental');
        }

        $this->log('Data Entities (Elements): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        $this->log($fileName . "\n");
    }

    /**
     * Returns all ElementalArea IDs that are referenced by pages published in
     * the given locale. Discovers eligible tables dynamically by looking for
     * *_Localised tables in the current database that have an ElementalAreaID column.
     *
     * @return int[]
     */
    private function getPublishedAreaIdsForLocale(string $locale): array
    {
        $dbName = Environment::getEnv('SS_DATABASE_NAME');
        $safeLocale = Convert::raw2sql($locale);

        // Find every *_Localised table that carries an ElementalAreaID column.
        $tables = DB::query("
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$dbName}'
              AND COLUMN_NAME   = 'ElementalAreaID'
              AND TABLE_NAME   LIKE '%\_Localised'
        ");

        $areaIds = [];
        foreach ($tables as $row) {
            $table = $row['TABLE_NAME'];
            $rows = DB::query(
                "SELECT DISTINCT `ElementalAreaID`
                 FROM `{$table}`
                 WHERE `Locale` = '{$safeLocale}'
                   AND `ElementalAreaID` > 0"
            );
            foreach ($rows as $r) {
                $areaIds[(int) $r['ElementalAreaID']] = true;
            }
        }

        return array_keys($areaIds);
    }

    public function getData($class, $locale = null, $filter = [])
    {
        $objects = Versioned::get_by_stage($class, 'Live');

        if($class === Page::class) {
            $objects = $objects->filter(['ShowInSearch' => true]);
        }

        if($filter) {
            $objects = $objects->filter($filter);
        }

        $data = [];
        foreach($objects as $object) {
            if(SearchConfig::isFluentEnabled()) {

                if ($object->getExtensionInstance(FluentVersionedExtension::class)) {
                    if($object->isPublishedInLocale($locale)) {
                        $data[] = $this->getSearchIndexOfDataObject($class, $object->ID);
                    }
                } elseif ($object->getExtensionInstance(FluentExtension::class)) {
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

        return array_filter($data);
    }

    private function getSearchIndexOfDataObject($class, $objectID)
    {
        $object = DataObject::get_by_id($class, $objectID);
        if (!$object) {
            return null;
        }

        if (!$object->getSearchIndexData()) {
            return null;
        }

        return $object->getSearchIndexData();
    }

    private function writeSearchFile($data, string $fileName)
    {
        // Check if folder exists
        if(!is_dir(SearchService::getIndexPath())) {
            mkdir(SearchService::getIndexPath(), 0777, true);
        }

        // Check if file exists and clean content
        if (file_exists($fileName)) {
            file_put_contents($fileName, '');
        }

        $file = fopen($fileName, 'w');
        fwrite($file, json_encode($data));
        fclose($file);
    }

    public function log($msg)
    {
        echo $msg . "\n";
    }
}
