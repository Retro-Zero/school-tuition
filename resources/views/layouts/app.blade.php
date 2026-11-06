<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>@yield('title', $year->name ?? config('school.name'))</title>

    <link id="style" href="{{ \App\Support\Asset::url('/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/style.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/plugins.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/icons.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.20/sweetalert2.min.css">
    @stack('styles')
</head>

<body class="app sidebar-mini ltr light-mode">

    <div class="page">
        <div class="page-main">
            <div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex align-items-center justify-content-between w-100 gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar"
                                href="javascript:void(0)"></a>
                            <div>
                                <div class="header-title">
                                    @hasSection('heading')
                                        @yield('heading')
                                    @elseif ($year)
                                        <x-year-name :year="$year" />
                                    @else
                                        سال‌های مالی
                                    @endif
                                </div>
                                <div class="header-sub">
                                    @hasSection('subheading')
                                        @yield('subheading')
                                    @elseif ($year)
                                        سال مالی <x-year-name :year="$year" />
                                    @else
                                        مدیریت سال‌های مالی
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if ($year)
                                <x-year-badge :year="$year" class="d-none d-md-inline" />
                            @endif
                            <span class="user-chip d-none d-sm-inline-flex">
                                <span class="avatar">{{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}</span>
                                {{ auth()->user()->name ?? '' }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light">خروج</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')
            <br>
        </div>
    </div>

    <div class="sticky">
        <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
        <div class="app-sidebar">
            <div class="side-header">
                <h3>{{ config('school.name') }}</h3>
            </div>
            <div class="main-sidemenu">
                <ul class="side-menu">
                    <li class="slide">
                        <a class="side-menu__item has-link {{ request()->routeIs('years.*') ? 'active' : '' }}" href="{{ route('years.index') }}">
                            <i class="side-menu__icon fe fe-calendar"></i>
                            <span class="side-menu__label">سال‌های مالی</span>
                        </a>
                    </li>

                    @if ($year)
                        @php($y = ['year' => $year])
                        <li class="slide">
                            <a class="side-menu__item has-link {{ request()->routeIs('report') ? 'active' : '' }}" href="{{ route('report', $y) }}">
                                <i class="side-menu__icon fe fe-bar-chart"></i>
                                <span class="side-menu__label">گزارش سال مالی</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a class="side-menu__item has-link {{ request()->routeIs('prices.*') ? 'active' : '' }}" href="{{ route('prices.edit', $y) }}">
                                <i class="side-menu__icon fe fe-dollar-sign"></i>
                                <span class="side-menu__label">تعرفه‌ها</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a class="side-menu__item has-link {{ request()->routeIs('coupons.index') ? 'active' : '' }}" href="{{ route('coupons.index', $y) }}">
                                <i class="side-menu__icon fe fe-percent"></i>
                                <span class="side-menu__label">کدهای تخفیف</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a class="side-menu__item has-link {{ request()->routeIs('payments.index') ? 'active' : '' }}" href="{{ route('payments.index', $y) }}">
                                <i class="side-menu__icon fe fe-credit-card"></i>
                                <span class="side-menu__label">لیست تراکنش‌ها</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a class="side-menu__item has-link {{ request()->routeIs('coupons.students') ? 'active' : '' }}" href="{{ route('coupons.students', $y) }}">
                                <i class="side-menu__icon fe fe-users"></i>
                                <span class="side-menu__label">لیست تخفیفی‌ها</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                                <i class="side-menu__icon fe fe-user"></i>
                                <span class="side-menu__label">دانش‌آموزان</span>
                                <i class="angle fe fe-chevron-left"></i>
                            </a>
                            <ul class="slide-menu">
                                <li class="panel sidetab-menu">
                                    <div class="panel-body tabs-menu-body p-0 border-0">
                                        <div class="tab-content">
                                            <div class="tab-pane active">
                                                <ul class="sidemenu-list">
                                                    <li><a href="{{ route('students.index', $y) }}" class="slide-item">کل دانش‌آموزان</a></li>
                                                    @foreach ($grades as $grade)
                                                        @foreach ($fields as $field)
                                                            <li>
                                                                <a href="{{ route('students.index', $y + ['grade' => $grade->name, 'field' => $field->name]) }}"
                                                                    class="slide-item">{{ $grade->name }} {{ $field->name }}</a>
                                                            </li>
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li class="slide">
                            <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                                <i class="side-menu__icon fa fa-money"></i>
                                <span class="side-menu__label">پرداختی‌های مقرر ماهانه</span>
                                <i class="angle fe fe-chevron-left"></i>
                            </a>
                            <ul class="slide-menu">
                                <li class="panel sidetab-menu">
                                    <div class="panel-body tabs-menu-body p-0 border-0">
                                        <div class="tab-content">
                                            <div class="tab-pane active">
                                                <ul class="sidemenu-list">
                                                    @foreach ($months as $month => $label)
                                                        <li>
                                                            <a href="{{ route('commitments.index', $y + ['month' => $month]) }}"
                                                                class="slide-item">{{ $label }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li class="slide">
                            <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                                <i class="side-menu__icon fe fe-users"></i>
                                <span class="side-menu__label">لیست بدهکاران</span>
                                <i class="angle fe fe-chevron-left"></i>
                            </a>
                            <ul class="slide-menu">
                                <li class="panel sidetab-menu">
                                    <div class="panel-body tabs-menu-body p-0 border-0">
                                        <div class="tab-content">
                                            <div class="tab-pane active">
                                                <ul class="sidemenu-list">
                                                    <li><a href="{{ route('debtors.index', $y) }}" class="slide-item">همه دانش‌آموزان</a></li>
                                                    @foreach ($grades as $grade)
                                                        @foreach ($fields as $field)
                                                            <li>
                                                                <a href="{{ route('debtors.index', $y + ['grade' => $grade->name, 'field' => $field->name]) }}"
                                                                    class="slide-item">{{ $grade->name }} {{ $field->name }}</a>
                                                            </li>
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li class="slide">
                            <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                                <i class="side-menu__icon fe fe-credit-card"></i>
                                <span class="side-menu__label">لیست پرداختی‌ها</span>
                                <i class="angle fe fe-chevron-left"></i>
                            </a>
                            <ul class="slide-menu">
                                <li class="panel sidetab-menu">
                                    <div class="panel-body tabs-menu-body p-0 border-0">
                                        <div class="tab-content">
                                            <div class="tab-pane active">
                                                <ul class="sidemenu-list">
                                                    <li><a href="{{ route('paid.index', $y) }}" class="slide-item">همه دانش‌آموزان</a></li>
                                                    @foreach ($grades as $grade)
                                                        @foreach ($fields as $field)
                                                            <li>
                                                                <a href="{{ route('paid.index', $y + ['grade' => $grade->name, 'field' => $field->name]) }}"
                                                                    class="slide-item">{{ $grade->name }} {{ $field->name }}</a>
                                                            </li>
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    {{-- Only what the screens use. The template shipped the full DataTables export
         stack (pdfmake + vfs_fonts + jszip + buttons = ~2 MB) and a theme switcher this
         app never renders; both are gone, worth ~2.1 MB per page load. --}}
    <script src="{{ \App\Support\Asset::url('/js/jquery.min.js') }}"></script>
    {{-- was 'boostrap.min.js' (typo inherited from V1) and 404'd: Bootstrap's JS never
         loaded. The bundle includes Popper. --}}
    <script src="{{ \App\Support\Asset::url('/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/js/table-data.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/p-scroll/perfect-scrollbar.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/p-scroll/pscroll-1.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/sidemenu/sidemenu.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/sidebar/sidebar.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/js/custom.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.20/sweetalert2.all.min.js"></script>

    <script>
        // The fixed header changes height with its content (wrapping title, user chip,
        // year badge), so the page offset is measured instead of hard-coded.
        (function () {
            const setHeaderHeight = () => {
                const header = document.querySelector('.app-header');
                if (header) {
                    document.documentElement.style.setProperty('--header-h', header.offsetHeight + 'px');
                }
            };
            setHeaderHeight();
            window.addEventListener('load', setHeaderHeight);
            window.addEventListener('resize', setHeaderHeight);
        })();

        // Persian strings for the DataTables plugin (V1 shipped English defaults)
        if (window.jQuery && jQuery.fn.dataTable) {
            jQuery.extend(true, jQuery.fn.dataTable.defaults, {
                language: {
                    search: 'جستجو:',
                    lengthMenu: 'نمایش _MENU_ ردیف',
                    info: 'نمایش _START_ تا _END_ از _TOTAL_ ردیف',
                    infoEmpty: 'موردی موجود نیست',
                    infoFiltered: '(فیلترشده از _MAX_ ردیف)',
                    zeroRecords: 'ردیفی یافت نشد',
                    emptyTable: 'داده‌ای موجود نیست',
                    paginate: { first: 'اول', last: 'آخر', next: 'بعدی', previous: 'قبلی' }
                }
            });
        }

        @if (session('status'))
            Swal.fire({
                title: "موفق",
                text: @json(session('status')),
                icon: "success",
                confirmButtonText: "ادامه"
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: "ناموفق",
                text: @json(session('error')),
                icon: "error",
                confirmButtonText: "ادامه"
            });
        @endif
    </script>

    @stack('scripts')
</body>

</html>
