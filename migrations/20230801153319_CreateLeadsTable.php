<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Phpmig\Migration\Migration;
use Integration\Model\Lead;

class CreateLeadsTable extends Migration
{
    public function up(): void
    {
        $schema = $this->getSchema();

        $schema->create(
            Lead::TABLE,
            function (Blueprint $table) {
                $table->increments('id');
                $table->json('data')->nullable(false);
            },
        );
    }

    public function down(): void
    {
        $schema = $this->getSchema();

        $schema->drop(Lead::TABLE);
    }

    private function getSchema(): Builder
    {
        $container = $this->getContainer();
        $db = $container['db'];

        return $db::schema();
    }
}
