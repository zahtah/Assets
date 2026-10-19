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
            Schema::table('users', function (Blueprint $table) {
                // اضافه کردن ستون بولین به نام 'allow_editing' با مقدار پیش‌فرض false
                // می‌توانید آن را بعد از ستون دیگری قرار دهید، مثلاً 'email'
                $table->boolean('allow_editing')->default(false)->after('email');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('users', function (Blueprint $table) {
                // حذف ستون در صورت بازگرداندن migration
                $table->dropColumn('allow_editing');
            });
        }
    };
