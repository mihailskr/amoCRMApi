<?php
declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Phpmig\Migration\Migration;
use Integration\Model\Lead;

class CreateNormalizeLeadsTable extends Migration
{
    public function up(): void
    {
        $schema = $this->getSchema();

        $schema->table(
            Lead::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('data');
                $table->integer('amo_lead_id')->nullable(false);
                $table->integer('account_id')->nullable(false);;
                $table->string('name')->nullable(false);
                $table->integer('budget')->nullable(false);
                $table->integer('responsible_user_id')->nullable(false);
                $table->integer('status_id')->nullable(false);
            },
        );
    }

    public function down(): void
    {
        $schema = $this->getSchema();

        $schema->table(
            Lead::TABLE,
            function (Blueprint $table) {
                $table->json('data')->nullable(false);
                $table->dropColumn('amo_lead_id');
                $table->dropColumn('account_id');
                $table->dropColumn('name');
                $table->dropColumn('budget');
                $table->dropColumn('responsible_id');
                $table->dropColumn('status_id');
            }
        );
    }

    private function getSchema(): Builder
    {
        $container = $this->getContainer();
        $db = $container['db'];

        return $db::schema();
    }
}
