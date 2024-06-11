<?php

declare(strict_types=1);

use Phpmig\Migration\Migration;
use CrmApi\Auth\Model\Token;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;


class NormalizeOAuthTokensTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $schema = $this->getSchema();

        $schema->table(Token::TABLE,
            function (Blueprint $table) {
                $table->unsignedInteger('account_id')->nullable(false);
                $table->string('subdomain', 1024)->nullable(false);
            }
        );
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $schema = $this->getSchema();

        $schema->table(Token::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('account_id');
                $table->dropColumn('subdomain');
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
