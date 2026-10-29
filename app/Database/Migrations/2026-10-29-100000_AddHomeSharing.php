<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHomeSharing extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'home' => ['type' => 'INT', 'unsigned' => true],
            'user' => ['type' => 'INT', 'unsigned' => true],
            'role' => ['type' => 'VARCHAR', 'constraint' => 16],
        ]);
        $this->forge->addKey(['home', 'user'], true);
        $this->forge->addKey('user');
        $this->forge->createTable('t_home_member');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'home' => ['type' => 'INT', 'unsigned' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'role' => ['type' => 'VARCHAR', 'constraint' => 16],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'expires_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash');
        $this->forge->addUniqueKey(['home', 'email']);
        $this->forge->createTable('t_home_invitation');
    }

    public function down()
    {
        $this->forge->dropTable('t_home_invitation');
        $this->forge->dropTable('t_home_member');
    }
}
