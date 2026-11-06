<?php

namespace App\Providers;

use App\Models\GradeLevel;
use App\Models\StudyField;
use App\Support\SchoolCalendar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @fa($value) prints a number in Persian digits (display only).
        Blade::directive('fa', fn (string $expression) => "<?php echo e(\\App\\Support\\PersianNumber::forDisplay($expression)); ?>");

        // The sidebar needs the current year, the grade/field taxonomy and the month
        // labels on every page. One composer, instead of V1's DB query inside the layout.
        View::composer('layouts.app', function ($view) {
            $view->with([
                'year' => request()->route()?->parameter('year'),
                'grades' => GradeLevel::orderBy('sort_order')->get(),
                'fields' => StudyField::orderBy('sort_order')->get(),
                'months' => SchoolCalendar::COMMITMENT_MONTHS,
            ]);
        });
    }
}
