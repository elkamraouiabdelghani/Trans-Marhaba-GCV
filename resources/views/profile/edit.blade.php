<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
            <div class="p-3 sm:p-4 row">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" style="margin-bottom: 40px;">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
    
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div id="toast" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header" style="background-color: #374151; color: white;">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body" style="background-color: white;">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
                <script>
                    setTimeout(function() {
                        const toast = document.getElementById('toast');
                        if (toast) {
                            toast.style.opacity = '0';
                            toast.style.transition = 'opacity 0.5s ease-in-out';
                            setTimeout(() => toast.remove(), 300);
                        }
                    }, 3000);
                </script>
            @endif
        </div>
    </div>
</x-app-layout>

<style>
    .row{
        display: flex;
        justify-content: space-between;
    }
    .row>div{
        max-width: 45%;
    }
    @media (max-width: 767px) {
        .row {
            display: block;
        }
        .row>div{
            max-width: 100%;
        }
    }
</style>