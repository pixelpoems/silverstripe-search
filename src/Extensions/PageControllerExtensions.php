<?php

namespace Pixelpoems\Search\Extensions;

use Pixelpoems\Search\Services\SearchService;
use SilverStripe\ORM\DataExtension;

class PageControllerExtensions extends DataExtension
{
    public function getSearchKeys(): string
    {
        return SearchService::getSearchKeysForTemplate();
    }
}
