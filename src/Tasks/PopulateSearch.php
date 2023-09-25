<?php
namespace Pixelpoems\Search\Tasks;

use DNADesign\Elemental\Models\BaseElement;
use Page;
use Pixelpoems\Search\Services\PopulateService;
use Pixelpoems\Search\Services\SearchConfig;
use ReflectionException;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use TractorCow\Fluent\Extension\FluentExtension;
use TractorCow\Fluent\Extension\FluentVersionedExtension;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

class PopulateSearch extends BuildTask
{
    protected $title = '[SEARCH] Populate';

    protected $description = 'Crate, Re-Create and prepare the silverstripe search index at each run.';

    private static string $segment = "search-populate";

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function run($request)
    {
        $service = PopulateService::create();

        if(SearchConfig::isFluentEnabled()) {
            $service->log("##########################################");
            $service->log("Fluent is enabled.");
            $service->log("##########################################\n");

            $exclude_locale_from_index = SearchConfig::getExcludedLocales();

            if($exclude_locale_from_index) {
                $locales = Locale::get()->exclude(['Locale' => $exclude_locale_from_index]);
                $service->log("There are some locales excluded from indexing: \n" . implode(', ', $exclude_locale_from_index));
                $service->log("##########################################\n");

            } else {
                $locales = Locale::get();
            }

            foreach ($locales as $locale) {
                FluentState::singleton()->withState(function(FluentState $state) use ($locale, $service) {
                    $state->setLocale($locale->Locale);
                    $service->log('START POPULATING: ' . $locale . "\n");
                    $service->populate($locale->Locale, $locale->Locale);
                    $service->log($locale . ': SUCCESS' . "\n");
                });
            }
        } else {
            $service->log("START POPULATING\n");
            $service->populate();
            $service->log('SUCCESS' . "\n");
        }

        $service->log("##########################################\n");
        $service->log('Successfully written search index!');
    }
}
