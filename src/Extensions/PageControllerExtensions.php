<?php

namespace Pixelpoems\FuseSearch\Extensions;

use Pixelpoems\FuseSearch\Services\SearchService;
use SilverStripe\ORM\DataExtension;

class PageControllerExtensions extends DataExtension
{
    public function getSearchKeys()
    {
        return SearchService::getSearchKeysForTemplate();
    }
}
