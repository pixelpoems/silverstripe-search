<?php
namespace Pixelpoems\Search\Services;

use Pixelpoems\Search\Controllers\SearchController;
use SilverStripe\Core\Config\Config;
use Pixelpoems\Search\Tasks\PopulateSearch;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;

class SearchService
{
    use Injectable;

    private $isInline = null;
    private $locale = null;
    private $value = '';

    public function __construct($value = '', $locale = null, $isInline = false)
    {
        $this->value = $value;
        $this->locale = $locale;

        // If isInline return max 10 results
        $this->isInline = $isInline;
    }

    public function getSearchResult()
    {
        $list = ArrayList::create();

        $src = $this->getSearchIndex();

        foreach (Config::inst()->get(PopulateSearch::class, 'index_keys') as $key) {
            foreach ($src as $item) {
                if (preg_match("/" . $this->value . "/i", $item->$key)) {
                    if (isset($item->class)) {
                        $entity = DataObject::get($item->class)->byID($item->id);
                        if ($entity && !array_keys($list->map()->keys(), $entity->ID) && $entity->canView()) $list->push($entity);

                    }
                }
            }
        };


//        $list->columnUnique('title');

        if($this->isInline) return $list->limit(10);
        return $list;


    }

    private function getSearchIndex()
    {
        if(Config::inst()->get(SearchController::class, 'enable_fluent')) {
            $fileName = self::getIndexFile($this->locale);

            // Check if file exists
            if(!file_exists($fileName)) {
                return;
            }


            $index = file_get_contents($fileName, '');
            $data = json_decode($index);


            if(file_exists(self::getIndexFile($this->locale . '-elemental'))) {
                $indexElemental = file_get_contents(self::getIndexFile($this->locale . '-elemental'), '');
                $data = array_merge($data, json_decode($indexElemental));
            }

            return $data;

        } else {
            // GET index.json
            // GET index-elemntal.json
        }
    }

    public static function getIndexPath(): string
    {
        return BASE_PATH . '/search/';
    }

    public static function getIndexFile($name = null): string
    {
        if($name) return self::getIndexPath() . $name . '.json';
        return self::getIndexPath();
    }

    public static function getSearchKeysForTemplate(): string
    {
        $keys = Config::inst()->get(PopulateSearch::class, 'index_keys');
        $keys = array_unique($keys);
        $keys = json_encode(array_values($keys));
        return htmlspecialchars($keys);
    }
}
