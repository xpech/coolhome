<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDataSeriesIndex extends Migration
{
    private const INDEX_NAME = 'idx_t_data_series_created';

    public function up()
    {
        $this->forge->addKey(['device', 'sensor', 'kind', 'created'], false, false, self::INDEX_NAME);
        $this->forge->processIndexes('t_data');
    }

    public function down()
    {
        $this->forge->dropKey('t_data', self::INDEX_NAME);
    }
}