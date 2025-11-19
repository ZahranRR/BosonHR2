<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE cash_advances MODIFY status ENUM('ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'ongoing'");
    }
    
    public function down()
    {
        DB::statement("ALTER TABLE cash_advances MODIFY status ENUM('ongoing', 'completed') NOT NULL DEFAULT 'ongoing'");
    }
    
};
