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
        // If no such *_Localised/*ElementalAreaID column exists at all, this Fluent
        // setup doesn't translate ElementalArea assignment (the common case: areas
        // are shared across locales, individual element fields are translated
        // instead). In that case the filter is skipped entirely - getData() already
        // applies correct per-locale filtering via Fluent's isPublishedInLocale()/
        // isPublished(), so there's nothing more to restrict here.
        $filter = [];
        if ($locale && SearchConfig::isFluentEnabled()) {
            $localisedAreaColumns = $this->getLocalisedElementalAreaColumns();
            if ($localisedAreaColumns) {
                $validAreaIds = $this->getPublishedAreaIdsForLocale($locale, $localisedAreaColumns);
                $this->log('Valid ElementalArea IDs for locale ' . $locale . ': ' . count($validAreaIds));
                // Pass an impossible match when the list is empty so no elements are indexed.
                $filter = ['ParentID' => $validAreaIds ?: [0]];
            }
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
     * Finds *_Localised tables that carry an *ElementalAreaID column, i.e. Fluent
     * setups where ElementalArea assignment itself is a translated field
     * (e.g. ElementalAreaID, GridElementalAreaID, …).
     *
     * An empty result means this setup doesn't translate ElementalArea assignment
     * at all, so callers should skip locale-based area filtering rather than
     * treating it as "no areas published for this locale".
     *
     * @return array<array{TABLE_NAME: string, COLUMN_NAME: string}>
     */
    private function getLocalisedElementalAreaColumns(): array
    {
        $dbName = Environment::getEnv('SS_DATABASE_NAME');

        $columns = [];
        $tables = DB::query("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$dbName}'
              AND COLUMN_NAME   LIKE '%ElementalAreaID'
              AND TABLE_NAME   LIKE '%\_Localised'
        ");
        foreach ($tables as $row) {
            $columns[] = $row;
        }

        return $columns;
    }

    /**
     * Returns all ElementalArea IDs that are referenced by pages published in
     * the given locale, using the given *_Localised/*ElementalAreaID columns
     * (see getLocalisedElementalAreaColumns()).
     *
     * @param array<array{TABLE_NAME: string, COLUMN_NAME: string}> $localisedAreaColumns
     * @return int[]
     */
    private function getPublishedAreaIdsForLocale(string $locale, array $localisedAreaColumns): array
    {
        $safeLocale = Convert::raw2sql($locale);

        $areaIds = [];
        foreach ($localisedAreaColumns as $row) {
            $table = $row['TABLE_NAME'];
            $column = $row['COLUMN_NAME'];
            $rows = DB::query(
                "SELECT DISTINCT `{$column}`
                 FROM `{$table}`
                 WHERE `Locale` = '{$safeLocale}'
                   AND `{$column}` > 0"
            );
            foreach ($rows as $r) {
                $areaIds[(int) $r[$column]] = true;
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
