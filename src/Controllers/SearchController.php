<?php

namespace Pixelpoems\FuseSearch\Controllers;

use Page;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use TractorCow\Fluent\State\FluentState;

class SearchController extends Controller
{
    private static array $allowed_actions = [
        'result'
    ];

    public function result(HTTPRequest $request): DBHTMLText|bool|string
    {
        if($request->getVar('locale')) {
            $requestHTMLLocale = Convert::raw2sql($request->getVar('locale'));
            $locale = str_replace('-', '_', $requestHTMLLocale);
        } else $locale = null;

        $ids = $request->getVar('ids');
        $pages = Page::get()->byIDs(explode(',', $ids));
        $pageArray = ArrayList::create();

        foreach ($pages as $page) {
            $pageArray->push($page);
        }

        return $this->getPageResponse($locale, $pageArray);
    }

    private function getPageResponse($locale, $pageArray)
    {
        return FluentState::singleton()->withState(function(FluentState $state) use ($locale, $pageArray) {
            $state->setLocale($locale);

            return $this->customise([
                'Pages' => $pageArray
            ])->renderWith('Pixelpoems\\FuseSearch\\Ajax\\SearchList');
        });
    }
}
