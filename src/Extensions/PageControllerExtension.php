<?php

namespace Pixelpoems\Search\Extensions;

use Pixelpoems\Search\Services\SearchConfig;
use SilverStripe\Core\Extension;

class PageControllerExtension extends Extension
{

    public function getSearchToggleAttr()
    {
        // Returns the class for the close button of the search inline
        $smToggle = SearchConfig::config()->get('enable_toggle_sm');
        $lgToggle = SearchConfig::config()->get('enable_toggle_lg');
        $toggleBreakpoint = SearchConfig::config()->get('sm_lg_breakpoint');

        $classes = [];
        if ($smToggle) $classes[] = 'data-search-toggle-sm=true';
        else $classes[] = 'data-search-toggle-sm=false';

        if ($lgToggle) $classes[] = 'data-search-toggle-lg=true';
        else $classes[] = 'data-search-toggle-lg=false';

        if ($toggleBreakpoint) $classes[] = 'data-search-toggle-breakpoint=' . $toggleBreakpoint;

        if (count($classes) > 0) {
            return implode(' ', $classes);
        }
        return '';
    }

}
