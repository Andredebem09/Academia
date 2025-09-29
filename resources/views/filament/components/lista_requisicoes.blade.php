<x-filament::page>
    <div x-data="kanbanBoard()" x-init="initBoard()" class="px-4 kanban-board">

        @php
            $statuses = [
                'pendente' => ['title' => 'Pendente', 'desc' => 'Chamados aguardando início do atendimento.'],
                'atendendo' => ['title' => 'Em Atendimento', 'desc' => 'Chamados em atendimento pela equipe.'],
                'aprovacao' => ['title' => 'Aprovação', 'desc' => 'Aguardando aprovação do gerente.'],
                'concluido' => ['title' => 'Concluído', 'desc' => 'Chamados finalizados com sucesso.'],
                'reprovado' => ['title' => 'Reprovado', 'desc' => 'Chamados não aprovados.'],
            ];

            $user = auth()->user();
            $unidades = \App\Models\Academia::all();
            $requisicoes = \App\Models\Requisicoes::query()
                ->when($user?->cargo === 'atendente', fn($q) => $q->whereIn('status', ['pendente', 'atendendo']))
                ->when($user?->cargo === 'gerente', fn($q) => $q->where('status', 'aprovacao'))
                ->get()
                ->groupBy('status');
        @endphp

        {{-- Filtros --}}
        <div class="flex flex-wrap gap-4 mb-6">
            <select x-model="filters.unidade" class="filter-select">
                <option value="">Todas as unidades</option>
                @foreach ($unidades as $unidade)
                    <option value="{{ $unidade->id }}">{{ $unidade->name }}</option>
                @endforeach
            </select>

            <select x-model="filters.emergencial" class="filter-select">
                <option value="">Todos</option>
                <option value="1">Emergenciais</option>
                <option value="0">Não emergenciais</option>
            </select>

            <input type="date" x-model="filters.data" class="filter-select">
        </div>

        {{-- Loading --}}
        <template x-if="loading">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 px-6 py-4 rounded-xl shadow-2xl animate-fade-in">
                    Salvando mudanças...
                </div>
            </div>
        </template>

        {{-- Kanban --}}
        <div class="flex gap-4 overflow-x-auto py-6">
            @foreach ($statuses as $statusKey => $statusData)
                <div
                    class="flex-shrink-0 w-96 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-4 flex flex-col hover:border-red-400 hover:shadow-lg transition">

                    {{-- Cabeçalho da coluna estilo Jira --}}
                    <div class="text-center mb-4 bg-gray-200 dark:bg-gray-700 rounded-md py-2 px-3">
                        <h2 class="text-sm font-bold uppercase text-gray-800 dark:text-gray-100 tracking-wide">
                            {{ $statusData['title'] }} ({{ count($requisicoes[$statusKey] ?? []) }})
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            {{ $statusData['desc'] }}
                        </p>
                    </div>

                    <div class="space-y-3 flex-1 min-h-[60px] kanban-column" data-status="{{ $statusKey }}">
                        @foreach ($requisicoes[$statusKey] ?? [] as $item)
                            <template x-if="showCard({{ $item->id }})">
                                <a href="{{ route('filament.admin.resources.requisicoes.view', $item) }}"
                                    class="relative block p-3 bg-white dark:bg-gray-900 rounded-lg shadow cursor-pointer hover:ring-2 hover:ring-red-400"
                                    data-id="{{ $item->id }}">

                                    @if ($item->emergencial)
                                        <div class="absolute left-0 top-0 h-full w-1 bg-red-500 rounded-l"></div>
                                    @endif

                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                            {{ $item->unidade->name ?? '—' }}
                                        </span>
                                        @if ($item->emergencial)
                                            <span
                                                class="text-xs px-2 py-0.5 bg-red-500 text-white rounded-full animate-pulse">⚡</span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2 line-clamp-3">
                                        {{ $item->relato }}
                                    </p>

                                    @if ($item->foto)
                                        <img src="{{ Storage::url($item->foto) }}"
                                            class="w-full h-24 object-cover rounded mb-2 hover:scale-105 transition-transform duration-300"
                                            alt="Foto">
                                    @endif

                                    {{-- Nota de Atendimento --}}
                                    @if ($item->nota_atendimento && in_array($item->status, ['aprovacao', 'reprovado', 'concluido']))
                                        <div class="text-xs text-blue-600 dark:text-blue-400 mb-1">
                                            Nota Atendimento: {{ $item->nota_atendimento }}
                                        </div>
                                    @endif

                                    {{-- Nota de Aprovação --}}
                                    @if ($item->nota_aprovacao && in_array($item->status, ['reprovado', 'concluido']))
                                        <div class="text-xs text-green-600 dark:text-green-400 mb-1">
                                            Nota Aprovação: {{ $item->nota_aprovacao }}
                                        </div>
                                    @endif

                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $item->created_at->format('H:i d/m/Y') }}
                                    </div>
                                </a>
                            </template>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
        <style>
            /* ======= Filtros ======= */
            .filter-select {
                display: inline-flex;
                width: auto;
                min-width: 120px;
                max-width: 100%;
                border: 1px solid #b91c1c;
                border-radius: 0.5rem;
                padding: 0.5rem 0.75rem;
                background-color: #111;
                color: #f3f4f6;
                transition: all 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
                cursor: pointer;
            }

            .filter-select:hover {
                border-color: #f87171;
                box-shadow: 0 4px 12px rgba(248, 113, 113, 0.5);
                transform: translateY(-2px) scale(1.02);
            }

            .filter-select:focus {
                outline: none;
                border-color: #f87171;
                box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.4);
                background-color: #1f1f1f;
                color: #f3f4f6;
            }

            .filter-select option {
                transition: background 0.2s ease;
            }

            .filter-select option:hover {
                background-color: rgba(248, 113, 113, 0.2);
            }

            /* Dark mode explícito */
            .dark .filter-select {
                background-color: #111;
                color: #f3f4f6;
                border-color: #b91c1c;
            }

            .dark .filter-select:focus {
                border-color: #f87171;
                box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.4);
            }

            .filter-select::placeholder {
                color: #9ca3af;
            }

            /* ======= Filtros responsivos ======= */
            @media (max-width: 768px) {
                .filter-select {
                    min-width: 100px;
                    padding: 0.4rem 0.6rem;
                    font-size: 0.875rem;
                }
            }

            @media (max-width: 480px) {
                .filter-select {
                    display: block;
                    width: 100%;
                    margin-bottom: 0.5rem;
                }
            }

            /* ======= Kanban ======= */
            .kanban-board {
                scroll-behavior: smooth;
            }

            .kanban-column {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .kanban-column:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            }

            .kanban-column a {
                transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            }

            .kanban-column a:hover {
                transform: translateY(-6px) scale(1.03);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
                background: linear-gradient(145deg, #1f1f1f, #2d2d2d);
            }

            /* Animação entrada dos cards */
            .kanban-column a {
                animation: fade-up 0.4s ease both;
            }

            @keyframes fade-up {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }

                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Scrollbar custom */
            .kanban-column::-webkit-scrollbar {
                height: 6px;
            }

            .kanban-column::-webkit-scrollbar-thumb {
                background: rgba(107, 114, 128, 0.6);
                border-radius: 3px;
            }

            /* Loading overlay */
            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }

            @keyframes fade-in {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            function kanbanBoard() {
                return {
                    loading: false,
                    filters: {
                        unidade: '',
                        emergencial: '',
                        data: ''
                    },
                    initBoard() {
                        this.$el.querySelectorAll('.kanban-column').forEach(column => {
                            new Sortable(column, {
                                group: 'shared',
                                animation: 200,
                                ghostClass: 'opacity-50 border-2 border-indigo-500',
                                dragClass: 'scale-105 shadow-2xl',
                                onStart: () => document.body.classList.add('dragging'),
                                onEnd: evt => {
                                    document.body.classList.remove('dragging');
                                    this.updateStatus(evt);
                                }
                            });
                        });
                    },
                    updateStatus(evt) {
                        const id = evt.item.dataset.id;
                        const newStatus = evt.to.dataset.status;
                        this.loading = true;
                        fetch(`{{ url('/requisicoes') }}/${id}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                status: newStatus
                            }),
                        }).then(res => {
                            if (!res.ok) throw new Error();
                            this.toast('Status atualizado!', 'success');
                        }).catch(() => {
                            this.toast('Erro ao salvar, tente novamente.', 'error');
                        }).finally(() => this.loading = false);
                    },
                    showCard(id) {
                        const card = @json($requisicoes->flatten()->keyBy('id'));
                        if (!card[id]) return false;
                        let c = card[id];
                        if (this.filters.unidade && c.unidade_id != this.filters.unidade) return false;
                        if (this.filters.emergencial !== '' && c.emergencial != this.filters.emergencial) return false;
                        if (this.filters.data && c.created_at.split('T')[0] != this.filters.data) return false;
                        return true;
                    },
                    toast(msg, type) {
                        const bg = type === 'success' ? 'bg-green-600' : 'bg-red-600';
                        const div = document.createElement('div');
                        div.textContent = msg;
                        div.className =
                            `${bg} text-white px-4 py-2 rounded-lg fixed bottom-6 right-6 shadow-lg animate-fade-in`;
                        document.body.appendChild(div);
                        setTimeout(() => div.remove(), 2500);
                    }
                }
            }
        </script>
    @endpush
</x-filament::page>
