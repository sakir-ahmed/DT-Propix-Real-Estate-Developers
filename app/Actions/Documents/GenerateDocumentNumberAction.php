<?php

declare(strict_types=1);

namespace App\Actions\Documents;

use App\Models\DocumentSeries;
use Illuminate\Support\Facades\DB;

/**
 * Sequential document numbers per series, without gaps, under a row lock.
 */
class GenerateDocumentNumberAction
{
    /**
     * @return string example BK-26-000001
     */
    public function handle(string $series, ?int $year = null): string
    {
        $year = $year ?? (int) now()->format('y');
        $key = $series.'-'.$year;

        return DB::transaction(function () use ($key, $series, $year): string {
            $row = DocumentSeries::query()
                ->where('name', $key)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DocumentSeries::query()->updateOrCreate(
                    ['name' => $key],
                    ['next_number' => 1],
                );
                $next = 1;
            } else {
                $next = $row->next_number;
            }

            DocumentSeries::query()
                ->where('name', $key)
                ->increment('next_number');

            return sprintf('%s-%02d-%06d', $series, $year, $next);
        });
    }
}
