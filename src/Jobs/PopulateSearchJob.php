<?php

namespace Pixelpoems\Search\Jobs;

use Pixelpoems\Search\Services\PopulateService;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

// symbiote/silverstripe-queuedjobs is optional - only define the job if it is installed
if (!class_exists(AbstractQueuedJob::class)) {
    return;
}

/**
 * Queued job version of the PopulateSearch task.
 *
 * Requires symbiote/silverstripe-queuedjobs.
 * Pass a repeat interval in seconds to the constructor to re-queue the job after each run,
 * e.g. new PopulateSearchJob(86400) for a daily index rebuild.
 */
class PopulateSearchJob extends AbstractQueuedJob
{
    public function __construct($repeat = null)
    {
        // Queued jobs are re-constructed without arguments, so only set when given
        if ($repeat !== null) {
            $this->repeat = (int) $repeat;
        }
    }

    public function getTitle()
    {
        return '[SEARCH] Populate search index';
    }

    public function getJobType()
    {
        return QueuedJob::QUEUED;
    }

    public function process()
    {
        PopulateService::create()
            ->setLogCallback(fn(string $msg) => $msg !== '' ? $this->addMessage($msg) : null)
            ->populateAll();

        $this->isComplete = true;
    }

    public function afterComplete()
    {
        parent::afterComplete();

        $repeat = (int) $this->repeat;
        if ($repeat > 0) {
            QueuedJobService::singleton()->queueJob(
                new static($repeat),
                date('Y-m-d H:i:s', time() + $repeat)
            );
        }
    }
}
