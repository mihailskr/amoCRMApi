<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Phpmig\Migration\Migration;
use Integration\Model\Lead;

class AddIndexesToNormalizeLeadsTable extends Migration
{
    public function up()
    {
        $schema = $this->getSchema();

        $schema->table(Lead::TABLE, function (Blueprint $table) {
            $table->index(['account_id', 'status_id'], 'leads_ix_account_id_status_id');
            $table->index(['account_id', 'amo_lead_id'], 'leads_ix_account_id_amo_lead_id');
            $table->index(['account_id', 'responsible_user_id'], 'leads_ix_account_id_responsible_user_id');
            $table->index(['account_id', 'status_id', 'responsible_user_id'], 'leads_ix_account_id_status_id_responsible_user_id');
        });
    }

    public function down()
    {
        $schema = $this->getSchema();

        $schema->table(Lead::TABLE, function (Blueprint $table) {
            $table->dropIndex('leads_ix_account_id_amo_lead_id');
            $table->dropIndex('leads_ix_account_id_status_id');
            $table->dropIndex('leads_ix_account_id_responsible_user_id');
            $table->dropIndex('leads_ix_account_id_status_id_responsible_user_id');
        });
    }

    private function getSchema(): Builder
    {
        $container = $this->getContainer();
        $db = $container['db'];

        return $db::schema();
    }
}
