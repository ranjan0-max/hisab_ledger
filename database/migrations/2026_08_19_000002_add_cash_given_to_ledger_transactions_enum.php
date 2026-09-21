<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add CASH_GIVEN to ledger_transactions.transaction_type ENUM.
     * CASH_GIVEN = when shopkeeper gives cash/advance to a regular customer.
     * This type was referenced in the codebase but was missing from the DB ENUM definition.
     */
    public function up(): void
    {
        // MySQL requires redefining the full ENUM to add a new value
        DB::statement("ALTER TABLE ledger_transactions MODIFY COLUMN transaction_type ENUM('SALE','CASH_GIVEN','CUSTOMER_PAYMENT','PURCHASE','SUPPLIER_PAYMENT','ADJUSTMENT') NOT NULL");
    }

    public function down(): void
    {
        // Remove CASH_GIVEN — only safe if no rows use this value
        DB::statement("ALTER TABLE ledger_transactions MODIFY COLUMN transaction_type ENUM('SALE','CUSTOMER_PAYMENT','PURCHASE','SUPPLIER_PAYMENT','ADJUSTMENT') NOT NULL");
    }
};
