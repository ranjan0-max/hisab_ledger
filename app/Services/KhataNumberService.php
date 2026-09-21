<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KhataNumberService
{
    public function availableFor(
        int $clientId,
        string $type,
        int $newLimit = 10
    ): array {
        // Sorted active numbers only — any number missing from this sequence
        // (deactivated OR never assigned at all) is available for reuse.
        $activeNumbers = DB::table('contacts')
            ->where('client_id', $clientId)
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('khata_number')
            ->pluck('khata_number')
            ->map(fn ($number) => (int) $number);

        $maxActiveNumber = (int) $activeNumbers->last();

        $reusableNumbers = [];
        $previous = 0;

        foreach ($activeNumbers as $number) {
            for ($gap = $previous + 1; $gap < $number; $gap++) {
                $reusableNumbers[] = $gap;
            }

            $previous = $number;
        }

        $firstNewNumber = $maxActiveNumber + 1;
        $lastNewNumber = $maxActiveNumber + $newLimit;

        return [
            'reusable' => $reusableNumbers,
            'new' => range($firstNewNumber, $lastNewNumber),
            'max_active_number' => $maxActiveNumber,
        ];
    }
}
