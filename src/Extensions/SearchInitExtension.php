<?php

namespace Pixelpoems\Search\Extensions;

use Pixelpoems\Search\Services\SearchConfig;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class SearchInitExtension extends Extension
{
    public function onBeforeInit()
    {
        Requirements::javascript('pixelpoems/silverstripe-search:client/dist/javascript/search.min.js');

        if ($this->getOwner()->response && SearchConfig::getIsDefaultStyleEnabled()) {
            Requirements::css('pixelpoems/silverstripe-search:client/dist/css/search.min.css');
        }
    }
}
