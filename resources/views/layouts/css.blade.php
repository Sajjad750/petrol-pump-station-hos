<!-- Bootstrap CSS (AdminLTE compatible version) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome -->
<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
<!-- Theme style -->
<link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
{{-- Sweet Alert 2 --}}
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
{{-- select2 --}}
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<!-- Laravel Echo -->
@vite(['resources/js/app.js'])

<!-- Custom Dark Mode Styles -->
<style>
    /* Dark Mode Variables */
    :root {
        --dark-bg-primary: #1a1a1a;
        --dark-bg-secondary: #2d2d2d;
        --dark-bg-tertiary: #3a3a3a;
        --dark-text-primary: #ffffff;
        --dark-text-secondary: #b3b3b3;
        --dark-border: #4a4a4a;
        --dark-input-bg: #2d2d2d;
        --dark-input-border: #4a4a4a;
        --dark-input-focus: #007bff;
    }

    /* Dark Mode Body */
    body.dark-mode {
        background-color: var(--dark-bg-primary) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Navbar */
    body.dark-mode .main-header.navbar {
        background-color: var(--dark-bg-secondary) !important;
        border-bottom: 1px solid var(--dark-border) !important;
    }

    body.dark-mode .main-header.navbar .navbar-nav .nav-link {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .main-header.navbar .navbar-nav .nav-link:hover {
        color: var(--dark-text-secondary) !important;
    }

    /* Dark Mode Sidebar */
    body.dark-mode .main-sidebar {
        background-color: var(--dark-bg-secondary) !important;
    }

    body.dark-mode .main-sidebar .brand-link {
        background-color: var(--dark-bg-tertiary) !important;
        border-bottom: 1px solid var(--dark-border) !important;
    }

    body.dark-mode .main-sidebar .brand-text {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .main-sidebar .user-panel .info a {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .main-sidebar .nav-sidebar .nav-link {
        color: var(--dark-text-secondary) !important;
    }

    body.dark-mode .main-sidebar .nav-sidebar .nav-link:hover {
        background-color: var(--dark-bg-tertiary) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .main-sidebar .nav-sidebar .nav-link.active {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Content */
    body.dark-mode .content-wrapper {
        background-color: var(--dark-bg-primary) !important;
    }

    body.dark-mode .content-header {
        background-color: var(--dark-bg-secondary) !important;
        border-bottom: 1px solid var(--dark-border) !important;
    }

    body.dark-mode .content-header h1 {
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Cards */
    body.dark-mode .card {
        background-color: var(--dark-bg-secondary) !important;
        border: 1px solid var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .card-header {
        background-color: var(--dark-bg-tertiary) !important;
        border-bottom: 1px solid var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .card-body {
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Tables */
    body.dark-mode .table {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .table thead th {
        background-color: var(--dark-bg-tertiary) !important;
        border-color: var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .table tbody tr {
        background-color: var(--dark-bg-secondary) !important;
    }

    body.dark-mode .table tbody tr:hover {
        background-color: var(--dark-bg-tertiary) !important;
    }

    body.dark-mode .table td,
    body.dark-mode .table th {
        border-color: var(--dark-border) !important;
    }

    /* Dark Mode Form Controls */
    body.dark-mode .form-control {
        background-color: var(--dark-input-bg) !important;
        border-color: var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .form-control:focus {
        background-color: var(--dark-input-bg) !important;
        border-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    body.dark-mode .form-control::placeholder {
        color: var(--dark-text-secondary) !important;
    }

    body.dark-mode .form-group label {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .form-check-label {
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Buttons */
    body.dark-mode .btn-primary {
        background-color: var(--dark-input-focus) !important;
        border-color: var(--dark-input-focus) !important;
    }

    body.dark-mode .btn-secondary {
        background-color: var(--dark-bg-tertiary) !important;
        border-color: var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .btn-outline-primary {
        color: var(--dark-input-focus) !important;
        border-color: var(--dark-input-focus) !important;
    }

    body.dark-mode .btn-outline-primary:hover {
        background-color: var(--dark-input-focus) !important;
        border-color: var(--dark-input-focus) !important;
    }

    /* Dark Mode Modals */
    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary) !important;
        border: 1px solid var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .modal-header {
        background-color: var(--dark-bg-tertiary) !important;
        border-bottom: 1px solid var(--dark-border) !important;
    }

    body.dark-mode .modal-footer {
        border-top: 1px solid var(--dark-border) !important;
    }

    /* Dark Mode Select2 */
    body.dark-mode .select2-container--default .select2-selection--single {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
        border: 1px solid var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
        height: 38px !important;
    }

    body.dark-mode .select2-container .select2-selection--single {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
        border: 1px solid var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-container--default .select2-selection {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
        border: 1px solid var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--dark-text-primary) !important;
        line-height: 36px !important;
        padding-left: 12px !important;
    }

    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--dark-text-secondary) !important;
    }

    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: var(--dark-text-secondary) transparent transparent transparent !important;
    }

    body.dark-mode .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent var(--dark-text-secondary) transparent !important;
    }

    body.dark-mode .select2-dropdown {
        background-color: var(--dark-bg-secondary) !important;
        border: 1px solid var(--dark-border) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
    }

    body.dark-mode .select2-container--default .select2-results__option {
        background-color: var(--dark-bg-secondary) !important;
        color: var(--dark-text-primary) !important;
        padding: 8px 12px !important;
    }

    body.dark-mode .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Select2 Option Hover Effect - Blue Background */
    body.dark-mode .select2-container--default .select2-results__option:hover {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-container--default .select2-results__option[aria-selected=true]:hover {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: var(--dark-bg-tertiary) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-container--default .select2-results__option--highlighted[aria-selected=true] {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Additional Select2 Hover Styles for Better Coverage */
    body.dark-mode .select2-dropdown .select2-results__option:hover {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-results__option:hover {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Select2 Option Transitions */
    body.dark-mode .select2-results__option {
        transition: background-color 0.2s ease !important;
    }

    body.dark-mode .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: var(--dark-input-bg) !important;
        border: 1px solid var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-container--default .select2-results__message {
        color: var(--dark-text-secondary) !important;
    }

    /* Select2 Focus States */
    body.dark-mode .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--dark-input-focus) !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    body.dark-mode .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: var(--dark-input-focus) !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    /* Select2 Active/Selected State - Blue Border */
    body.dark-mode .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--dark-input-focus) !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    body.dark-mode .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: var(--dark-input-focus) !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    /* Select2 Hover State */
    body.dark-mode .select2-container--default .select2-selection--single:hover {
        border-color: var(--dark-input-focus) !important;
    }

    body.dark-mode .select2-container--default .select2-selection--multiple:hover {
        border-color: var(--dark-input-focus) !important;
    }

    /* Select2 Loading State */
    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered .select2-selection__clear {
        color: var(--dark-text-secondary) !important;
    }

    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered .select2-selection__clear:hover {
        color: var(--dark-text-primary) !important;
    }

    /* Additional Select2 Dark Mode Overrides */
    body.dark-mode .select2-container {
        background-color: var(--dark-input-bg) !important;
    }

    body.dark-mode .select2-container--default.select2-container--focus .select2-selection--single {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
    }

    body.dark-mode .select2-container--default.select2-container--open .select2-selection--single {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
    }

    body.dark-mode .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: var(--dark-bg-tertiary) !important;
        background-image: none !important;
    }

    /* Force override for all Select2 selection elements */
    body.dark-mode .select2-container * {
        background-color: inherit !important;
    }

    body.dark-mode .select2-container .select2-selection {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
    }

    body.dark-mode .select2-container .select2-selection--single {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
    }

    body.dark-mode .select2-container .select2-selection--multiple {
        background-color: var(--dark-input-bg) !important;
        background-image: none !important;
    }

    /* Dark Mode Select2 Multiple Selection */
    body.dark-mode .select2-container--default .select2-selection--multiple {
        background-color: var(--dark-input-bg) !important;
        border: 1px solid var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
        min-height: 38px !important;
    }

    body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        color: var(--dark-text-primary) !important;
        padding: 2px 5px !important;
    }

    body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--dark-bg-tertiary) !important;
        border: 1px solid var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
        padding: 2px 8px !important;
    }

    body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: var(--dark-text-secondary) !important;
        margin-right: 5px !important;
    }

    body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        background: transparent !important;
        border: none !important;
        color: var(--dark-text-primary) !important;
        padding: 0 !important;
    }

    body.dark-mode .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
        color: var(--dark-text-secondary) !important;
    }

    /* Dark Mode Select2 Optgroups */
    body.dark-mode .select2-container--default .select2-results__group {
        background-color: var(--dark-bg-tertiary) !important;
        color: var(--dark-text-primary) !important;
        font-weight: bold !important;
        padding: 8px 12px !important;
        border-bottom: 1px solid var(--dark-border) !important;
    }

    body.dark-mode .select2-container--default .select2-results__option[role=group] {
        padding: 0 !important;
    }

    body.dark-mode .select2-container--default .select2-results__option[role=group] .select2-results__options--nested .select2-results__option {
        padding-left: 20px !important;
    }

    /* Dark Mode Select2 Loading */
    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered .select2-selection__placeholder {
        color: var(--dark-text-secondary) !important;
    }

    /* Dark Mode Select2 Disabled State */
    body.dark-mode .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: var(--dark-bg-tertiary) !important;
        color: var(--dark-text-secondary) !important;
        cursor: not-allowed !important;
    }

    body.dark-mode .select2-container--default.select2-container--disabled .select2-selection--multiple {
        background-color: var(--dark-bg-tertiary) !important;
        color: var(--dark-text-secondary) !important;
        cursor: not-allowed !important;
    }

    /* Dark Mode DataTables */
    body.dark-mode .dataTables_wrapper .dataTables_length,
    body.dark-mode .dataTables_wrapper .dataTables_filter,
    body.dark-mode .dataTables_wrapper .dataTables_info,
    body.dark-mode .dataTables_wrapper .dataTables_processing,
    body.dark-mode .dataTables_wrapper .dataTables_paginate {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .dataTables_wrapper .dataTables_filter input {
        background-color: var(--dark-input-bg) !important;
        border: 1px solid var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .dataTables_wrapper .dataTables_length select {
        background-color: var(--dark-input-bg) !important;
        border: 1px solid var(--dark-input-border) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Pagination */
    body.dark-mode .pagination .page-link {
        background-color: var(--dark-bg-secondary) !important;
        border-color: var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .pagination .page-link:hover {
        background-color: var(--dark-bg-tertiary) !important;
        border-color: var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .pagination .page-item.active .page-link {
        background-color: var(--dark-input-focus) !important;
        border-color: var(--dark-input-focus) !important;
    }

    /* Dark Mode Alerts */
    body.dark-mode .alert {
        background-color: var(--dark-bg-secondary) !important;
        border: 1px solid var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Breadcrumbs */
    body.dark-mode .breadcrumb {
        background-color: var(--dark-bg-tertiary) !important;
    }

    body.dark-mode .breadcrumb-item a {
        color: var(--dark-text-secondary) !important;
    }

    body.dark-mode .breadcrumb-item.active {
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Footer */
    body.dark-mode .main-footer {
        background-color: var(--dark-bg-secondary) !important;
        border-top: 1px solid var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Toggle Icon Animation */
    #darkModeIcon {
        transition: transform 0.3s ease;
    }

    body.dark-mode #darkModeIcon {
        transform: rotate(180deg);
    }

    /* Dark Mode Small Boxes */
    body.dark-mode .small-box {
        background-color: var(--dark-bg-secondary) !important;
        border: 1px solid var(--dark-border) !important;
    }

    body.dark-mode .small-box .inner h3,
    body.dark-mode .small-box .inner p {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .small-box .small-box-footer {
        background-color: var(--dark-bg-tertiary) !important;
        border-top: 1px solid var(--dark-border) !important;
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .small-box .small-box-footer:hover {
        background-color: var(--dark-input-focus) !important;
        color: var(--dark-text-primary) !important;
    }

    /* Dark Mode Badges */
    body.dark-mode .badge {
        color: var(--dark-text-primary) !important;
    }

    body.dark-mode .badge-success {
        background-color: #28a745 !important;
    }

    body.dark-mode .badge-warning {
        background-color: #ffc107 !important;
        color: var(--dark-bg-primary) !important;
    }

    body.dark-mode .badge-danger {
        background-color: #dc3545 !important;
    }

    body.dark-mode .badge-info {
        background-color: #17a2b8 !important;
    }

    /* Dark Mode Small Box Icons */
    body.dark-mode .small-box .icon i {
        color: rgba(255, 255, 255, 0.3) !important;
    }

    /* Dark Mode Transitions */
    body.dark-mode * {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease !important;
    }
</style>
