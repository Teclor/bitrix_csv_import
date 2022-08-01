<?php

namespace Sprint\Migration;


class TASK_2_AGENT_IMPORT20220801033631 extends Version
{
    protected $description = "This migration creates csv to iblock import agent using sprint.migration module";

    protected $moduleVersion = "4.1.1";

    /**
     * @return bool|void
     * @throws Exceptions\HelperException
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Agent()->saveAgent([
            'MODULE_ID' => 'iblock',
            'USER_ID' => null,
            'SORT' => '0',
            'NAME' => 'Custom\\Agents\\Import::runProductsFromCsvImport();',
            'ACTIVE' => 'Y',
            'NEXT_EXEC' => '02.08.2022 03:33:12',
            'AGENT_INTERVAL' => '86400',
            'IS_PERIOD' => 'N',
            'RETRY_COUNT' => '0',
        ]);
    }

    public function down()
    {
        $helper = $this->getHelperManager();
        $helper->Agent()->deleteAgent('iblock', 'Custom\\Agents\\Import::runProductsFromCsvImport();');
    }
}
