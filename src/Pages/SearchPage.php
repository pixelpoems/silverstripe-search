<?php

namespace Pixelpoems\Search\Pages;

use Pixelpoems\Search\Controllers\SearchPageController;
use SilverStripe\ORM\DataObject;

class SearchPage extends \Page
{
    private static string $table_name = 'Search_SearchPage';

    private static string $singular_name = 'Search Page';

    private static string $plural_name = 'Search Pages';

    private static string $icon_class = 'font-icon-search';

    private static array $allowed_children = [];

    private static array $defaults = [
        'ShowInMenus' => false,
        'ShowInSearch' => false
    ];

    public function getControllerName(): string
    {
        return SearchPageController::class;
    }

    public static function find_link($urlSegment = false)
    {
        $page = self::get_if_search_page_exists();
        if (!$page) return null;
        return ($urlSegment) ? $page->URLSegment : $page->Link();
    }

    protected static function get_if_search_page_exists(): ?DataObject
    {
        if ($page = DataObject::get_one(self::class)) {
            return $page;
        }
        return null;
    }
}
