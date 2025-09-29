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
    <div class="flex items-center justify-between">
        <a href="{{ \App\Filament\Resources\UserResource::getUrl('index') }}"
            class="back-button group inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 mr-2 transform transition-transform duration-300 group-hover:-translate-x-1"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Voltar
        </a>
    </div>

    <style>
        .back-button {
            transition: all 0.3s ease-in-out;
        }

        .back-button:hover {
            background-color: #b91c1c; /* tom mais forte de vermelho */
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .back-button svg {
            transition: transform 0.3s ease-in-out;
        }

        .back-button:hover svg {
            transform: translateX(-5px);
        }

        @media (max-width: 640px) {
            .back-button {
                width: 100%;
                justify-content: center;
                font-size: 0.875rem;
                padding: 0.5rem;
            }

            .back-button svg {
                margin-right: 0.5rem;
            }
        }
    </style>

    

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
