<?php

namespace IctCollege\Stagemarkt\Shell;

use App\Model\Entity\Company;
use App\Model\Entity\Position;
use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use DebugKit\DebugTimer;

class CompaniesShell extends Shell
{

    /**
     * Start an import of companies
     *
     * @return void
     */
    public function import()
    {
        $import = $this->Tasks->load('IctCollege/Stagemarkt.CompaniesImport');
        $import->initialize();

        ConnectionManager::alias('shard_' . $this->args[0], 'default');

        $conditions = [];
        if (isset($this->params['study_program_id'])) {
            $conditions['study_program_id'] = (int)$this->params['study_program_id'];
        }

        $import->import($conditions);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $consoleOptionParser = parent::getOptionParser();

        $consoleOptionParser->addOption('study_program_id');
        $consoleOptionParser->addSubcommand('import')->addArgument('department');

        return $consoleOptionParser;
    }
}
