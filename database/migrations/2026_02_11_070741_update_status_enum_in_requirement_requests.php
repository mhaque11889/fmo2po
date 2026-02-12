<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SQLite doesn't support ALTER TABLE to modify CHECK constraints,
     * so we need to recreate the table with the new constraint.
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('PRAGMA foreign_keys=off');

        // Begin transaction
        DB::beginTransaction();

        try {
            // Create new table with updated status enum
            DB::statement('
                CREATE TABLE requirement_requests_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    created_by INTEGER NOT NULL,
                    item VARCHAR(255) NOT NULL,
                    dimensions VARCHAR(255),
                    qty INTEGER NOT NULL,
                    location VARCHAR(255) NOT NULL,
                    remarks TEXT,
                    status VARCHAR(255) CHECK(status IN (\'pending\', \'approved\', \'assigned\', \'in_progress\', \'completed\', \'rejected\')) DEFAULT \'pending\',
                    approved_by INTEGER,
                    approved_at DATETIME,
                    assigned_to INTEGER,
                    assigned_by INTEGER,
                    assigned_at DATETIME,
                    progress_remarks TEXT,
                    progress_at DATETIME,
                    completion_remarks TEXT,
                    completed_at DATETIME,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ');

            // Copy data from old table to new table
            DB::statement('
                INSERT INTO requirement_requests_new
                SELECT * FROM requirement_requests
            ');

            // Drop old table
            DB::statement('DROP TABLE requirement_requests');

            // Rename new table to original name
            DB::statement('ALTER TABLE requirement_requests_new RENAME TO requirement_requests');

            // Commit transaction
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys=on');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks
        DB::statement('PRAGMA foreign_keys=off');

        DB::beginTransaction();

        try {
            // Create table with original status enum (without in_progress)
            DB::statement('
                CREATE TABLE requirement_requests_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    created_by INTEGER NOT NULL,
                    item VARCHAR(255) NOT NULL,
                    dimensions VARCHAR(255),
                    qty INTEGER NOT NULL,
                    location VARCHAR(255) NOT NULL,
                    remarks TEXT,
                    status VARCHAR(255) CHECK(status IN (\'pending\', \'approved\', \'assigned\', \'completed\', \'rejected\')) DEFAULT \'pending\',
                    approved_by INTEGER,
                    approved_at DATETIME,
                    assigned_to INTEGER,
                    assigned_by INTEGER,
                    assigned_at DATETIME,
                    progress_remarks TEXT,
                    progress_at DATETIME,
                    completion_remarks TEXT,
                    completed_at DATETIME,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ');

            // Copy data, converting in_progress back to assigned
            DB::statement('
                INSERT INTO requirement_requests_old
                SELECT id, created_by, item, dimensions, qty, location, remarks,
                    CASE WHEN status = \'in_progress\' THEN \'assigned\' ELSE status END,
                    approved_by, approved_at, assigned_to, assigned_by, assigned_at,
                    progress_remarks, progress_at, completion_remarks, completed_at,
                    created_at, updated_at
                FROM requirement_requests
            ');

            DB::statement('DROP TABLE requirement_requests');
            DB::statement('ALTER TABLE requirement_requests_old RENAME TO requirement_requests');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::statement('PRAGMA foreign_keys=on');
    }
};
