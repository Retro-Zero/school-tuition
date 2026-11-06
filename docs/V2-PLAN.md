# V2 plan — what this rewrite is and why

V1 (`master` in the same repository) is a Laravel 10 app that ran the school's
tuition bookkeeping. It worked, but four things made it unsafe to put on the
internet and expensive to change: no authentication, CSRF disabled, deletes behind
GET links, and a schema where prices, subjects and months were columns with Persian
names. V2 keeps the screens and the CSS and replaces the foundation.

Built on **Laravel 12** (not 11: 11 is out of security support since March 2026 and
Composer 2.11 refuses to install it — 8 open advisories). Same Blade/Blade-component
approach as V1, so the UI work carried over.

## Schema map

| V1 | V2 | why |
|---|---|---|
| `users` (the students!) | `students` | the auth table is now `users` again, for staff |
| `users.status` enum | `students.is_active` bool | one concept, not two |
| `prices.دهم/یازدهم/دوازدهم` | `base_tuitions(price_book_id, grade_level_id, amount)` | grades are rows |
| `prices.ten_summer/ten_extra/...` | `unit_prices(price_book_id, grade_level_id, term, amount)` | same for per-session rates |
| `prices.insurance` | `price_books.insurance_amount` | one row per year |
| `users.summer_physics … extra_philosophy` (14 cols) | `enrolments(student_id, subject_id, term, quantity)` | enrolments are rows |
| `payment_agreement.mehr … khordad` (9 cols) | `commitments(student_id, due_on, amount, label)` | months are rows |
| `transactions` | `payments` | clearer name; soft deletes |
| `coupons.price` | `coupons.amount` | "price" was ambiguous |
| `varchar` money | `BIGINT` (signed) | arithmetic, SUM, and the negative carry-over case |

`users.council`, `users.olympiad`, `users.debt` became `students.council_amount`,
`students.olympiad_amount`, `students.carry_over_debt` (signed: the imported data has a
−7,000,000 ریال credit for one student).

## Structure

```
app/Actions/       one class per write (StoreStudent, UpdateStudent, RecordPayment,
                   UpdatePayment, EnsurePriceBook) — the overpayment rule lives once
app/Queries/       StudentQuery (filters, totals, page, owing/paying/discounted),
                   ReportSummary (cached report), StudentFormData
app/Http/Requests/ FinancialYearRequest, StudentRequest, PaymentRequest,
                   CouponRequest, PriceRequest
app/Policies/      StudentPolicy — a viewer reads everything, changes nothing
app/Rules/         JalaliDate (accepts 1402/08/18)
app/Import/        V1Importer + ParityReport, usable without the console
app/Services/      Tuition, TuitionLine, TuitionSummary
app/Support/       Jalali, SchoolCalendar, Asset (filemtime cache busting)
routes/            web.php (auth, years, prices) + app.php (/y/{year}/…)
```

Reads of a roster all go through `StudentQuery`, so the eager loads, the lean
totals query and the tuition decoration cannot drift apart between screens.
`Tuition::forYearId()` is read-only; only `EnsurePriceBook` may create a price book,
so a GET can no longer INSERT one.

## The single source of tuition truth

`App\Services\Tuition` (+ `TuitionLine`, `TuitionSummary`) is the only place the formula
exists. V1 had it in six methods and it had already diverged. The formula is pinned by
`tests/Unit/TuitionTest.php` (5 tests, including the inactive-student branch and the
negative carry-over).

```
active   : base(grade) − discount + insurance + extras + council + olympiad + carry_over
inactive : extras + council + olympiad + carry_over
balance  : charges − payments
```

## Import + verification

`php artisan v1:import` reads the old database over a read-only `v1` connection, wipes
the V2 tables and rebuilds them. `php artisan v1:import --verify-only` prints V1's own
figures next to V2's:

```
students           207                207                OK
active students    203                203                OK
charges (ریال)     15,090,329,060     15,090,329,060     OK
payments (ریال)    5,135,868,360      5,135,868,360      OK
balance (ریال)     9,954,460,700      9,954,460,700      OK
```

Those five numbers are the acceptance criterion for the migration: if they match, no
student's balance moved.

## Security changes

- `auth` middleware on every application route; `/login` is the only public page.
- Login by username (no email dependency), single shared admin seeded from `.env`
  (`php artisan admin:create`), password hashed by the model cast.
- CSRF enforced (Laravel default), every form has `@csrf`.
- No destructive GET: delete/toggle are `DELETE`/`PATCH` with `@method`, confirmed in
  the browser.
- Soft deletes on `students` and `payments`; foreign keys use `RESTRICT` so deleting a
  student can never cascade away their payment history.
- All input validated (national ID uniqueness per year, amounts, enums, Jalali dates).

## UI layer (`public/css/app.css`)

The Sash template shipped `font-family: vazirmatn` but never declared the font, so
Persian text was falling back to whatever the OS had. V2 self-hosts Vazirmatn
(400/500/700 woff2 under `public/fonts/vazirmatn/`, no CDN — the production host is in
Iran) and adds one small app stylesheet on top of the template:

- type scale, ink/muted colours, hairline borders, 14px card radius;
- `x-stat-card` Blade component (icon, label, value, hint, four tones) used by the
  report (3×3), the student list and the student page;
- tables: quiet headers, row hover, settled rows tinted, tabular figures;
- forms, buttons, sidebar active state, a sticky translucent header with school + year;
- Persian DataTables strings, including the search placeholder the template hardcoded;
- print rules so the statement prints without app chrome.

The UI layer never touches the numbers: the report still renders
203 / 15,090,329,060 / 5,135,868,360 / 9,954,460,700.

## Still open (deliberately, for after go-live)

- Roles/permissions — `users.role` and `StudentPolicy` exist (admin/accountant/viewer),
  but there is no staff-management screen yet: accounts are made with `admin:create`.
- SMS notifications (`smsir`) and PDF receipts (`dompdf`) were installed in V1 but never
  called. Not carried over; `students.statement` is printable HTML, which is what the
  school actually used.
- Server-side pagination now covers students, debtors and payers. The debtors/payers
  lists still compute balances in PHP before slicing, so a very large roster would want
  a SQL balance column (denormalised) and a JSON endpoint for DataTables.
- A parent/student read-only view.
