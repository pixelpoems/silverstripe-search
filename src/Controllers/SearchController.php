<?php

namespace Pixelpoems\Search\Controllers;

use DNADesign\Elemental\Models\BaseElement;
use Page;
use Pixelpoems\Search\Pages\SearchPage;
use Pixelpoems\Search\Services\SearchService;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use TractorCow\Fluent\State\FluentState;

class SearchController extends Controller
{
    private static bool $enable_fluent = false;
    private static bool $enable_elemental = false;

    private static array $allowed_actions = [
        'result'
    ];

    public function result(HTTPRequest $request): DBHTMLText|bool|string
    {
        if($this->config()->get('enable_fluent') && $request->getVar('locale')) {
            $requestHTMLLocale = Convert::raw2sql($request->getVar('locale'));
            $locale = str_replace('-', '_', $requestHTMLLocale);
        } else $locale = null;

        $value = Convert::raw2sql($request->getVar('value'));
        $isInline = Convert::raw2sql($request->getVar('inline')) === 'true';

        if($this->config()->get('enable_fluent') && $locale) {
            $list = FluentState::singleton()->withState(function (FluentState $state) use ($locale, $value, $isInline) {
                $state->setLocale($locale);

                $search = SearchService::create($value, $locale, $isInline);
                return $search->getSearchResult();
            });

        } else {

            $search = SearchService::create($value, $locale, $isInline);
            $list = $search->getSearchResult();
        }

        $this->extend('updateList', $list);

        return $this->generateResponse($locale, $list, $isInline);
    }


    private function generateResponse($locale, $list, $isInline = false)
    {
        $data = [
            'List' => $list,
            'IsInline' => $isInline,
            'SearchPageLink' => Director::absoluteURL(SearchPage::find_link())
        ];

        $this->extend('updateAjaxTemplateData', $data);

        if($this->config()->get('enable_fluent')) {
            return FluentState::singleton()->withState(function(FluentState $state) use ($locale, $data) {
                $state->setLocale($locale);

                return $this->customise($data)->renderWith('Pixelpoems\\Search\\Ajax\\SearchList');
            });
        } else {
            return $this->customise($data)->renderWith('Pixelpoems\\Search\\Ajax\\SearchList');
        }
    }
}
