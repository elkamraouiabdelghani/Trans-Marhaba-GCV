<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ ucfirst(Auth::user()->role) }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Section -->

            <!-- Recent Activity Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Recent Activity') }}</h3>
                    <div class="text-gray-600">
                        {{ __("You're logged in!") }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <style>
        .grid {
            display: grid;
        }
        
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        @media (min-width: 768px) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        
        @media (min-width: 1024px) {
            .lg\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        
        .gap-6 {
            gap: 1.5rem;
        }
    </style>

    <!-- Chart.js and Plugins (load once) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gender Distribution Chart
        @if(($maleStudentsCount ?? 0) != 0 || ($femaleStudentsCount ?? 0) != 0)
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['male', 'female'],
                datasets: [{
                    data: [{{ $maleStudentsCount }}, {{ $femaleStudentsCount }}],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',  // Blue for male
                        'rgba(255, 99, 132, 0.8)'   // Pink for female
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 14 },
                        formatter: function(value, context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return percentage + '%';
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
        @endif

        // Groups & Students Bar Chart
        const ctx = document.getElementById('groupsStudentsBarChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Groups', 'Students'],
                datasets: [{
                    label: 'Count',
                    data: [{{ $groupsCount ?? 0 }}, {{ $studentsCount ?? 0 }}],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)', // Indigo for groups
                        'rgba(139, 92, 246, 0.8)'  // Purple for students
                    ],
                    borderRadius: 8,
                    maxBarThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold', size: 16 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: { stepSize: 1, font: { size: 14 } }
                    }
                }
            }
        });
    });
    </script>
</x-app-layout>
