<?php
namespace Pixelpoems\Search\Services;

use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\ArrayData;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataObject;

class SearchService extends Controller
{
    use Injectable;
    private string $locale = '';

    public function __construct(private string $value = '', $locale = '', private bool $isInline = false)
    {
        if ($locale) {
            $this->locale = $locale;
        }
    }

    /**
     * Escape HTML from string to prevent errors
     * @param string $string
     * @return string
     */
    public static function escapeHTML(string $string): string
    {
        $update = strip_tags($string);
        $update = str_replace("&nbsp;", '', $update);
        $update = str_replace("\n", ' ', $update);
        return str_replace("\/", ' ', $update);
    }

    public function getSearchResult()
    {
        $list = ArrayList::create();

        $src = $this->getSearchIndex();
        if (!$src) {
            return $list;
        }
        if (!SearchConfig::getSearchKeys() || !$src) {
            return $list;
        }

        foreach (SearchConfig::getSearchKeys() as $key) {
            foreach ($src as $item) {
                if(is_array($item->$key)) { // Prevent Error on wrong creation of object
                   continue;
                }

                if ($this->value && $item->$key) {
                    $search = preg_quote(trim($this->value), '/');
                    $text = preg_replace('/\s+/', ' ', (string) $item->$key); // Normalize spaces

                    $pregMatch = preg_match(sprintf('/%s/i', $search), (string) $text);
                } else {
                    $pregMatch = false;
                }


                if (($this->value && $item->$key) && isset($item->class) && $pregMatch) {
                    $entity = DataObject::get($item->class)->byID($item->id);
                    // Check if Entity exists and current member can view it
                    if ($entity) {

                        $canView = $entity->hasMethod('canView') ? $entity->canView() : true;
                        if (!$canView) {
                            continue;
                        }

                        $entity->UniqueID = $item->class . '--' . $item->id;
                        // Check if Entity does not already exist in list
                        $existingIDs = $list->map('UniqueID', 'UniqueID')->toArray();
                        if (!in_array($entity->UniqueID, $existingIDs)) {
                            $list->push($entity);
                            if ($this->isInline && $list->count() >= SearchConfig::getMaxResultsInline()) {
                                break;
                            }
                        }
                    }
                } elseif (($this->value && $item->$key) && isset($item->link) && $pregMatch) {
                    // If there is a link given this is no DataObject just a custom item
                    // Check if Entity does not already exist in list
                    $existingIDs = $list->map('UniqueID', 'UniqueID')->toArray();
                    if (!in_array(sprintf('%s--%s', $item->class, $item->id), $existingIDs)) {
                        $obj = new ArrayData();
                        $obj->UniqueID = $item->class . '--' . $item->id;
                        $obj->ID = $item->id;
                        $obj->Title = $item->title;
                        $obj->Content = $item->content;
                        $obj->Link = $item->link;
                        $list->push($obj);
                    }
                }

                if ($list->count() > 50) {
                    break;
                } // To prevent too many results
            }

            if ($this->isInline && $list->count() >= SearchConfig::getMaxResultsInline()) {
                break;
            }
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

        if(SearchConfig::isFluentEnabled() && $this->locale) {
            if(!$this->locale) {
                $this->locale = SearchConfig::getDefaultLocale();
            }
            // Change name to locale if fluent is enabled
            $name = $this->locale;
        }

        $fileName = self::getIndexFile($name);

        // Check if file exists
        if(!file_exists($fileName)) {
            return null;
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
        if ($name) {
            return self::getIndexPath() . $name . '.json';
        }

        return self::getIndexPath();
    }
}
