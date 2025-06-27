<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing 'role' string column if it exists
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            // Add role_id foreign key column
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete()->after('email');
            // nullable() in case you want to create users without roles initially
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');

            // Add back the 'role' string column (optional default length 255)
            $table->string('role')->nullable()->after('email');
        });
    }
};
