<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('messages', function (Blueprint $table) {
        // Mengubah receiver_id agar boleh kosong (nullable) karena pada chat grup tidak ada receiver individu
        $table->foreignId('receiver_id')->nullable()->change();
        
        // Menambahkan kolom group_id setelah kolom receiver_id
        $table->foreignId('group_id')->nullable()->after('receiver_id')->constrained()->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('messages', function (Blueprint $table) {
        $table->dropForeign(['group_id']);
        $table->dropColumn('group_id');
        $table->foreignId('receiver_id')->nullable(false)->change();
    });
}
};
