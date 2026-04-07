<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('invoice_number', 50)->unique();
            $table->string('client_name', 150);
            $table->string('client_email', 150)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid', 'unpaid'])->default('pending');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('organization_id');
            $table->index('project_id');
            $table->index('created_by');
            $table->index('status');
            $table->index('is_archived');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};