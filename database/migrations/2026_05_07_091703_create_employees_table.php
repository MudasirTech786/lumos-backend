      <?php

      use Illuminate\Database\Migrations\Migration;
      use Illuminate\Database\Schema\Blueprint;
      use Illuminate\Support\Facades\Schema;

      return new class extends Migration
      {
      public function up(): void
      {
            Schema::create('employees', function (Blueprint $table) {

                  $table->id();

                  // BASIC
                  $table->string('employee_code')
                        ->unique();

                  $table->string('name');

                  $table->string('email')
                        ->nullable();

                  $table->string('phone')
                        ->nullable();

                  // JOB INFO
                  $table->string('department')
                        ->nullable();

                  $table->string('designation')
                        ->nullable();

                  // PAYROLL
                  $table->decimal('base_salary', 10, 2)
                        ->default(0);

                  // EMPLOYMENT
                  $table->date('hire_date')
                        ->nullable();

                  $table->enum('status', [
                  'active',
                  'inactive',
                  'terminated'
                  ])->default('active');

                  // PERSONAL
                  $table->string('cnic')
                        ->nullable();

                  $table->text('address')
                        ->nullable();

                  $table->string('emergency_contact')
                        ->nullable();

                  // MEDIA
                  $table->string('profile_photo')
                        ->nullable();

                  $table->timestamps();
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('employees');
      }
      };