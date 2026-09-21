<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:prune-old-audit-logs')]
#[Description('Delete audit logs older than 30 days')]
class PruneOldAuditLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = \App\Models\AuditLog::where('created_at', '<', now()->subDays(30))->delete();
        $this->info("Deleted {$deleted} audit logs older than 30 days.");
    }
}
