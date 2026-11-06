<?php

namespace App\Http\Controllers;

use App\Actions\Pricing\EnsurePriceBook;
use App\Http\Requests\PriceRequest;
use App\Models\BaseTuition;
use App\Models\FinancialYear;
use App\Models\GradeLevel;
use App\Models\UnitPrice;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    public function edit(FinancialYear $year, EnsurePriceBook $ensurePriceBook)
    {
        $book = $ensurePriceBook->handle($year);

        $grades = GradeLevel::orderBy('sort_order')->get();

        $base = $book->baseTuitions->pluck('amount', 'grade_level_id');
        $units = $book->unitPrices->keyBy(fn ($p) => $p->grade_level_id.'|'.$p->term);

        return view('prices.edit', compact('year', 'book', 'grades', 'base', 'units'));
    }

    public function update(PriceRequest $request, FinancialYear $year, EnsurePriceBook $ensurePriceBook)
    {
        $data = $request->validated();
        $book = $ensurePriceBook->handle($year);

        DB::transaction(function () use ($book, $data) {
            $book->update(['insurance_amount' => (int) $data['insurance']]);

            foreach ($data['base'] as $gradeId => $amount) {
                BaseTuition::updateOrCreate(
                    ['price_book_id' => $book->id, 'grade_level_id' => (int) $gradeId],
                    ['amount' => (int) $amount],
                );
            }

            foreach ($data['units'] as $key => $amount) {
                [$gradeId, $term] = explode('|', $key);
                UnitPrice::updateOrCreate(
                    ['price_book_id' => $book->id, 'grade_level_id' => (int) $gradeId, 'term' => $term],
                    ['amount' => (int) $amount],
                );
            }
        });

        return redirect()->route('prices.edit', $year)->with('status', 'تعرفه‌ها ذخیره شد.');
    }
}
