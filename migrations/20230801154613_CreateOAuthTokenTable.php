<?php

declare(strict_types=1);

use Phpmig\Migration\Migration;
use CrmApi\Auth\Model\Token;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

class CreateOAuthTokenTable extends Migration
{
    public function up(): void
    {
        $schema = $this->getSchema();

        $schema->create(
            Token::TABLE,
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('access_token', 1024)->nullable(false);
                $table->integer('expires_at')->nullable(false);
                $table->string('refresh_token', 1024)->nullable(true);
            },
        );
    }

    public function down(): void
    {
        $schema = $this->getSchema();

        $schema->drop(Token::TABLE);
    }

    private function getSchema(): Builder
    {
        $container = $this->getContainer();
        $db = $container['db'];

        return $db::schema();
    }
}
