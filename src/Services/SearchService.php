<?php
namespace Pixelpoems\Search\Services;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;

class SearchService extends Controller
{
    use Injectable;

    private bool $isInline;
    private string $locale;
    private string $value;

    public function __construct($value = '', $locale = '', $isInline = false)
    {
        $this->value = $value;
        if($locale) $this->locale = $locale;

        // If isInline return max results
        // Defined in Config
        $this->isInline = $isInline;
    }
    public function getSearchResult()
    {
        $list = ArrayList::create();

        $src = $this->getSearchIndex();
        if(!$src) return $list;

        if(!SearchConfig::getSearchKeys() || !$src) return $list;
        foreach (SearchConfig::getSearchKeys() as $key) {
            foreach ($src as $item) {
                if (isset($item->class) && $item->$key && preg_match("/" . $this->value . "/i", $item->$key)) {
                    $entity = DataObject::get($item->class)->byID($item->id);

                    // Check if Entity exists and current member can view it
                    if ($entity && $entity->canView()) {

                        // Check if Entity does not already exist in list
                        if(!array_keys($list->map()->keys(), $entity->ID)) {
                            $list->push($entity);
                            if($this->isInline && $list->count() >= SearchConfig::getMaxResultsInline()) break;
                        }
                    }
                }
            }
            if($this->isInline && $list->count() >= SearchConfig::getMaxResultsInline()) break;
        };

        $this->extend('updateSearchResultBeforeLimit', $list);
        if($this->isInline) {
            $list = $list->limit(SearchConfig::getMaxResultsInline());
            $this->extend('updateSearchResultAfterLimit', $list);
        }

        $this->extend('updateSearchResult', $list);

        return $list;
    }

    private function getSearchIndex()
    {
        $name = 'index';

        if(SearchConfig::isFluentEnabled()) {
            // Change name to locale if fluent is enabled
            $name = $this->locale;
        }

        $fileName = self::getIndexFile($name);

        // Check if file exists
        if(!file_exists($fileName)) {
            return;
        }

        $index = file_get_contents($fileName, '');
        $data = json_decode($index);

        // Check if elemental index file exists
        if(SearchConfig::isElementalEnabled() && file_exists(self::getIndexFile($name . '-elemental'))) {
            $indexElemental = file_get_contents(self::getIndexFile($name . '-elemental'), '');
            $data = array_merge($data, json_decode($indexElemental));
        }

        return $data;
    }

    public static function getIndexPath()
    {
        return BASE_PATH . '/search/';
    }

    public static function getIndexFile($name = null)
    {
        if($name) return self::getIndexPath() . $name . '.json';
        return self::getIndexPath();
    }
}
