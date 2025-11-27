<?php
namespace Pixelpoems\Search\Tasks;

use Override;
use Pixelpoems\Search\Services\PopulateService;
use Pixelpoems\Search\Services\SearchConfig;
use ReflectionException;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Input\InputInterface;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;
use Symfony\Component\Console\Command\Command;

class PopulateSearch extends BuildTask
{
    protected string $title = '[SEARCH] Populate';

    protected static string $description = 'Create, Re-Create and prepare the silverstripe search index at each run.';

    private static string $segment = "search-populate";

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        return $this->run($input, $output);
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function run(InputInterface $input, PolyOutput $output): int
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
                FluentState::singleton()->withState(function(FluentState $state) use ($locale, $service): void {
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

        return Command::SUCCESS;
    }
}
