<?php

namespace IctCollege\Stagemarkt\Shell\Task;

use App\Model\Entity\Company;
use App\Model\Entity\Position;
use App\Model\Entity\StudyProgram;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use DebugKit\DebugTimer;
use Muffin\Webservice\Query;

class PositionsImportTask extends Shell
{

    public function initialize()
    {
        parent::initialize();

        $this->modelFactory('Endpoint', ['Muffin\Webservice\Model\EndpointRegistry', 'get']);

        $this->loadModel('IctCollege/Stagemarkt.Positions', 'Endpoint');
    }

    public function import($conditions)
    {
        $positionsTable = TableRegistry::get('Positions');

        $results = $this->_importReadQuery($conditions)->count();
        $pages = ceil($results / 25);

        $timeEstimate = $pages * 1;
        $this->out(__('Importing {0} positions. This will take approximately {1} hours', $results, Time::createFromTimestamp($timeEstimate)->format('H:m:s')));

        $progress = $this->helper('Progress');

        $progress->init(['total' => $pages]);

        DebugTimer::start('positions-import');

        $this->io()->setLoggers(false);

        for ($page = 1; $page <= $pages; $page++) {
            $query = $this->_importReadQuery($conditions);
            $query->applyOptions(['page' => $page]);
            $resources = $query->all();

            /* @var \Muffin\Webservice\Model\Resource $resource */
            foreach ($resources as $resource) {
                if ($positionsTable->exists(['stagemarkt_id' => $resource->id])) {
                    $localPosition = $positionsTable->find()->where(['stagemarkt_id' => $resource->id])->first();
                    $localPosition->applyResource($resource);
                } else {
                    $localPosition = Position::createFromResource($resource);
                }

//                $remoteDetailPosition = $this->Positions->get($remoteEntity->id);
//                $localPosition->applyResource($remoteDetailPosition);

                if ($resource->has('company')) {
                    if ($positionsTable->Companies->exists(['stagemarkt_id' => $resource->company->id])) {
                        $localCompany = $positionsTable->Companies->find()->where(['stagemarkt_id' => $resource->company->id])->first();
                        $localCompany->applyResource($resource->company);
                    } else {
                        $localCompany = Company::createFromResource($resource->company);
                    }

                    $localPosition->set([
                        'company' => $localCompany
                    ]);
                }
                if ($resource->has('study_program')) {
                    if ($positionsTable->StudyPrograms->exists(['id' => $resource->study_program->id])) {
                        $localStudyProgram = $positionsTable->StudyPrograms->find()->where(['id' => $resource->study_program->id])->first();
                        $localStudyProgram->applyResource($resource->study_program);
                    } else {
                        $localStudyProgram = StudyProgram::createFromResource($resource->study_program);
                    }

                    $localPosition->set([
                        'study_program' => $localStudyProgram
                    ]);
                }

                $positionsTable->save($localPosition, [
                    'associated' => [
                        'Companies',
                        'StudyPrograms'
                    ],
                    'atomic' => false
                ]);
            }

            $progress->increment();
            $progress->draw();
        }

        $this->out();

        $this->io()->setLoggers(true);

        DebugTimer::stop('positions-import');

        $duration = DebugTimer::elapsedTime('positions-import');
        $this->out(__('Import took {0}, that\'s {1} off the estimate', Time::createFromTimestamp($duration)->format('H:m:s'), Time::createFromTimestamp(abs($duration - $timeEstimate))->format('H:m:s')));
    }

    public function getOptionParser()
    {
        $consoleOptionParser = parent::getOptionParser();

        $consoleOptionParser->addOption('study_program_id');

        return $consoleOptionParser;
    }

    /**
     * @return Query
     */
    protected function _importReadQuery(array $conditions = [])
    {
        $query = $this->Positions->find();
        $query->limit(25);
        $query->where($conditions);

        return $query;
    }

}
