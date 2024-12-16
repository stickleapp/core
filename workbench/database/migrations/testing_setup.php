<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var string
     */
    private $prefix = 'lc_';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->prefix;

        Schema::table('users', function (Blueprint $table) {
            $table->integer('votes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = $this->prefix;
    }
};
