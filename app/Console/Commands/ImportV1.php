<?php

namespace App\Console\Commands;

use App\Import\ParityReport;
use App\Import\V1Importer;
use App\Models\FinancialYear;
use App\Models\Student;
use App\Services\Tuition;
use Illuminate\Console\Command;

/**
 * Thin wrapper: the mapping lives in App\Import\V1Importer, the comparison in
 * App\Import\ParityReport (both usable from tests without the console).
 */
class ImportV1 extends Command
{
    protected $signature = 'v1:import {--verify-only : only print the parity check, change nothing}';

    protected $description = 'Import the V1 (soltani5) database into the V2 schema and verify the totals';

    public function handle(ParityReport $parity): int
    {
        if (! $this->option('verify-only')) {
            (new V1Importer(function (int $done, int $total, string $label) {
                if ($done === $total) {
                    $this->line("  {$label}: {$done}");
                }
            }))->run();

            $this->info('Import finished.');
            $this->newLine();
            $this->printImportedTotals();
        }

        return $this->printParity($parity);
    }

    private function printImportedTotals(): void
    {
        foreach (FinancialYear::orderBy('id')->get() as $year) {
            $students = Student::forYear($year->id)
                ->with(['enrolments', 'coupon'])
                ->withSum('payments', 'amount')
                ->get();

            $summary = Tuition::forYearId($year->id)->decorate($students);

            $this->line(sprintf(
                '%s  students=%d active=%d charges=%s paid=%s balance=%s',
                $year->name,
                $summary->students,
                $summary->activeStudents,
                number_format($summary->charges),
                number_format($summary->paid),
                number_format($summary->balance()),
            ));
        }
    }

    private function printParity(ParityReport $parity): int
    {
        $this->info('V1 (source) vs V2 (imported)');
        $this->line(str_repeat('-', 72));

        foreach ($parity->rows() as $row) {
            $this->line(sprintf(
                '%-18s %-18s %-18s %s',
                $row['label'],
                number_format($row['v1']),
                number_format($row['v2']),
                $row['ok'] ? 'OK' : 'MISMATCH',
            ));
        }

        $this->line(str_repeat('-', 72));
        $this->line($parity->matches() ? 'Parity: exact match.' : 'Parity: DIFFERENCES FOUND.');

        return $parity->matches() ? self::SUCCESS : self::FAILURE;
    }
}
