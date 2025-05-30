    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('drivers', function (Blueprint $table) {
                $table->string('password')->after('email');
                $table->rememberToken(); // adds nullable remember_token column
            });
        }

        public function down(): void
        {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('password');
                $table->dropColumn('remember_token');
            });
        }
    };
