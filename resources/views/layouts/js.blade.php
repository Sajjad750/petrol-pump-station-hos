<!-- jQuery -->
<script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap 4 -->
<script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('dist/js/adminlte.min.js')}}"></script>
<!-- DataTables  & Plugins -->
<script src="{{asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/jszip/jszip.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
<!-- Sweet Alert 2 -->
<script src="{{asset('plugins/sweetalert2/sweetalert2.all.js')}}"></script>
<script src="{{asset('plugins/moment/moment.min.js')}}"></script>
<script src="{{asset('plugins/moment/moment-with-locales.js')}}"></script>
<script src="{{asset('plugins/select2/js/select2.full.js')}}"></script>

<script>
    function alertMsg(msg, status = 'success') {
        Swal.fire({
            icon: status,
            title: msg,
            showConfirmButton: false,
            timer: 1500,
        })
    }

    // Dark Mode Functionality
    function initDarkMode() {
        // Check for saved dark mode preference or default to light mode
        const darkMode = localStorage.getItem('darkMode') === 'true';
        
        if (darkMode) {
            document.body.classList.add('dark-mode');
            document.getElementById('darkModeIcon').className = 'fas fa-sun';
        } else {
            document.body.classList.remove('dark-mode');
            document.getElementById('darkModeIcon').className = 'fas fa-moon';
        }
        
        // Reinitialize Select2 after dark mode is applied
        setTimeout(function() {
            $('.select2').select2('destroy').select2({
                theme: 'bootstrap4'
            });
            
            // Force apply dark mode styles after initialization
            setTimeout(function() {
                if (document.body.classList.contains('dark-mode')) {
                    $('.select2-container--default .select2-selection--single').css({
                        'background-color': '#2d2d2d',
                        'background-image': 'none',
                        'border-color': '#4a4a4a',
                        'color': '#ffffff'
                    });
                    $('.select2-container--default .select2-selection--multiple').css({
                        'background-color': '#2d2d2d',
                        'background-image': 'none',
                        'border-color': '#4a4a4a',
                        'color': '#ffffff'
                    });
                }
            }, 50);
        }, 200);
    }

    // Toggle dark mode
    function toggleDarkMode() {
        const body = document.body;
        const icon = document.getElementById('darkModeIcon');
        
        if (body.classList.contains('dark-mode')) {
            // Switch to light mode
            body.classList.remove('dark-mode');
            icon.className = 'fas fa-moon';
            localStorage.setItem('darkMode', 'false');
        } else {
            // Switch to dark mode
            body.classList.add('dark-mode');
            icon.className = 'fas fa-sun';
            localStorage.setItem('darkMode', 'true');
        }
        
        // Reinitialize Select2 to apply new styles
        setTimeout(function() {
            $('.select2').select2('destroy').select2({
                theme: 'bootstrap4'
            });
            
            // Force apply dark mode styles after reinitialization
            setTimeout(function() {
                if (document.body.classList.contains('dark-mode')) {
                    $('.select2-container--default .select2-selection--single').css({
                        'background-color': '#2d2d2d',
                        'background-image': 'none',
                        'border-color': '#4a4a4a',
                        'color': '#ffffff'
                    });
                    $('.select2-container--default .select2-selection--multiple').css({
                        'background-color': '#2d2d2d',
                        'background-image': 'none',
                        'border-color': '#4a4a4a',
                        'color': '#ffffff'
                    });
                }
            }, 50);
        }, 200);
    }

    // Dark mode event listener
    document.addEventListener('DOMContentLoaded', function() {
        initDarkMode();
        
        document.getElementById('darkModeToggle').addEventListener('click', function(e) {
            e.preventDefault();
            toggleDarkMode();
        });
    });

    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        })

        // Global Select2 dark mode hover effects
        $(document).on('select2:open', '.select2', function() {
            // Ensure proper styling is applied when dropdown opens
            setTimeout(function() {
                if (document.body.classList.contains('dark-mode')) {
                    $('.select2-results__option').each(function() {
                        if ($(this).attr('aria-selected') === 'true') {
                            $(this).css({
                                'background-color': '#3a3a3a',
                                'color': '#ffffff'
                            });
                        } else {
                            $(this).css({
                                'background-color': '#2d2d2d',
                                'color': '#ffffff'
                            });
                        }
                    });
                }
            }, 50);
        });

        $(document).on('submit', '.restore_form', function (e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: "Are you sure?",
                text: "You want to restore this record?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, restore it!"
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: $(form).attr('method'),
                        url: $(form).attr('action'),
                        data: new FormData(form),
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            Swal.showLoading();
                        },
                        success: function (response) {
                            Swal.close();
                            $('#table').DataTable().ajax.reload();
                            alertMsg(response.message, response.status);
                        },
                        error: function (xhr, error, status) {
                            Swal.close();
                            var response = xhr.responseJSON;
                            alertMsg(response.message, 'error');
                        },
                    });
                }
            });
        });

        // $(document).on('shown.bs.modal', '.modal', function () {
        //     $('.select2').select2({
        //         theme: 'bootstrap4'
        //     });
        // });
    })

</script>
