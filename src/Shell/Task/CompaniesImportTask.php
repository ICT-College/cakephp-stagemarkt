<?php

namespace IctCollege\Stagemarkt\Shell\Task;

use App\Model\Entity\Company;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use DebugKit\DebugTimer;
use Muffin\Webservice\Query;

/**
 * @property \IctCollege\Stagemarkt\Model\Endpoint\CompaniesEndpoint Companies
 */
class CompaniesImportTask extends Shell
{

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->modelFactory('Endpoint', ['Muffin\Webservice\Model\EndpointRegistry', 'get']);

        $this->loadModel('IctCollege/Stagemarkt.Companies', 'Endpoint');
    }

    /**
     * Imports conditions with specified conditions
     *
     * @param array $conditions The conditions for positions to match
     *
     * @return void
     */
    public function import($conditions)
    {
        $companiesTable = TableRegistry::get('Companies');

        $results = $this->_importReadQuery($conditions)->count();
        $pages = ceil($results / 25);

        $timeEstimate = $pages * 1;
        $this->out(__('Importing {0} companies. This will take approximately {1} hours', $results, Time::createFromTimestamp($timeEstimate)->format('H:m:s')));

        $progress = $this->helper('Progress');

        $progress->init(['total' => $pages]);

        DebugTimer::start('companies-import');

        $this->io()->setLoggers(false);

        for ($page = 1; $page <= $pages; $page++) {
            $query = $this->_importReadQuery($conditions);
            $query->applyOptions(['page' => $page]);
            $resources = $query->all();

            /* @var \Muffin\Webservice\Model\Resource $resource */
            foreach ($resources as $resource) {
                if ($companiesTable->exists(['stagemarkt_id' => $resource->id])) {
                    $localCompany = $companiesTable->find()->where(['stagemarkt_id' => $resource->id])->first();
                    $localCompany->applyResource($resource);
                } else {
                    $localCompany = Company::createFromResource($resource);
                }

                $companiesTable->save($localCompany, [
                    'atomic' => false
                ]);
            }

            $progress->increment();
            $progress->draw();
        }

        $this->out();

        $this->io()->setLoggers(true);

        DebugTimer::stop('companies-import');

        $duration = DebugTimer::elapsedTime('positions-import');
        $this->out(__('Import took {0}, that\'s {1} off the estimate', Time::createFromTimestamp($duration)->format('H:m:s'), Time::createFromTimestamp(abs($duration - $timeEstimate))->format('H:m:s')));
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $consoleOptionParser = parent::getOptionParser();

        $consoleOptionParser->addOption('study_program_id');

        return $consoleOptionParser;
    }

    /**
     * @param array $conditions The set of conditions to apply
     *
     * @return Query
     */
    protected function _importReadQuery(array $conditions = [])
    {
        return $this->Companies->find()
            ->limit(25)
            ->where($conditions);
    }
}
