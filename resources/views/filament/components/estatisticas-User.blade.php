<x-filament::page class="space-y-12">

    {{-- Bootstrap CSS --}}
    @push('styles')
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
            crossorigin="anonymous"
        >
        <style>
            .user-card {
                transition: all 0.3s ease;
            }
            .user-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 25px rgba(0,0,0,0.1);
            }
            .chart-container {
                background: var(--tw-bg-opacity, #ffffff);
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            }
        </style>
    @endpush

    {{-- Botão Voltar --}}
    <div class="mt-4">
        <a href="{{ \App\Filament\Resources\UserResource::getUrl('index') }}"
           class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <x-heroicon-s-arrow-left class="w-4 h-4" />
            Voltar
        </a>
    </div>

    {{-- Cards com informações do usuário --}}
    <div class="row g-6">
        <div class="col-md-4">
            <div class="user-card p-4 bg-white dark:bg-gray-900 border rounded-3xl text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase">Requisições</p>
                <h2 class="mt-2 text-3xl font-extrabold text-primary-600 dark:text-primary-400">
                    {{ optional($this->user?->requisicoes)->count() ?? 0 }}
                </h2>
                <x-heroicon-o-document-text class="w-8 h-8 text-primary-500 dark:text-primary-400 mt-3 mx-auto" />
            </div>
        </div>
    </div>

    <div class="row g-6">

         <div class="col-md-4">
            <div class="user-card p-4 bg-white dark:bg-gray-900 border rounded-3xl text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase">Cargo</p>
                <h2 class="mt-2 text-3xl font-extrabold text-primary-600 dark:text-primary-400">
                    {{ ucfirst($this->user->cargo) }}
                </h2>
                <x-heroicon-o-briefcase class="w-8 h-8 text-primary-500 dark:text-primary-400 mt-3 mx-auto" />
            </div>
        </div>


    </div>

    <div class="row g-6">

        <div class="col-md-4">
            <div class="user-card p-4 bg-white dark:bg-gray-900 border rounded-3xl text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase">Data de criação</p>
                <h2 class="mt-2 text-3xl font-extrabold text-primary-600 dark:text-primary-400">
                    {{ $this->user->created_at->format('d/m/Y') }}
                </h2>
                <x-heroicon-o-calendar class="w-8 h-8 text-primary-500 dark:text-primary-400 mt-3 mx-auto" />
            </div>
        </div>
    </div>

    {{-- Gráfico de Requisições --}}
    <div class="chart-container mt-8">
        <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Requisições nos últimos dias</h3>
        <canvas id="userTasksChart" class="w-100 h-64"></canvas>
    </div>

    {{-- Scripts --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"
        ></script>

        <script>
            const isDark = document.documentElement.classList.contains('dark');

            const ctx = document.getElementById('userTasksChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(
                        $this->user?->requisicoes?->pluck('created_at')
                            ->map(fn($d) => $d->format('d/m')) ?? []
                    ) !!},
                    datasets: [{
                        label: 'Requisições',
                        data: {!! json_encode($this->user?->requisicoes?->pluck('id') ?? []) !!},
                        borderColor: isDark ? 'rgba(96, 165, 250, 1)' : 'rgba(59, 130, 246, 1)',
                        backgroundColor: isDark ? 'rgba(96, 165, 250, 0.2)' : 'rgba(59, 130, 246, 0.2)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: isDark ? '#E5E7EB' : '#1E3A8A'
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: isDark ? '#E5E7EB' : '#1E40AF' },
                            grid: { color: isDark ? 'rgba(55, 65, 81, 0.3)' : 'rgba(203, 213, 225, 0.3)' }
                        },
                        y: {
                            ticks: { color: isDark ? '#E5E7EB' : '#1E40AF' },
                            grid: { color: isDark ? 'rgba(55, 65, 81, 0.3)' : 'rgba(203, 213, 225, 0.3)' }
                        }
                    }
                }
            });
        </script>
    @endpush

</x-filament::page>
