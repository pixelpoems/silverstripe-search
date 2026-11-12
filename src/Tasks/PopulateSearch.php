<?php
namespace Pixelpoems\Search\Tasks;

use Pixelpoems\Search\Services\PopulateService;
use ReflectionException;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Input\InputInterface;
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
        PopulateService::create()->populateAll();

        return Command::SUCCESS;
    }
}
