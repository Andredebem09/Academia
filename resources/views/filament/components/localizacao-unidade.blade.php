<x-filament::page class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <a href="{{ \App\Filament\Resources\UnidadeResource::getUrl('index') }}"
            class="back-button group inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 mr-2 transform transition-transform duration-300 group-hover:-translate-x-1"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Voltar
        </a>
    </div>

    {{-- Endereço --}}
    @php
        $endereco = implode(
            ', ',
            array_filter([
                $record->name,
                $record->rua,
                $record->bairro,
                $record->cidade, 
                $record->estado, 
                $record->cep
            ]),
        );
    @endphp

    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
        <p class="text-gray-800 dark:text-gray-300 font-medium text-lg">Endereço:</p>
        <p class="text-gray-800 dark:text-gray-100">{{ $endereco }}</p>
    </div>

    {{-- Mapa --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden relative">
    {{-- Overlay inicial para evitar scroll da página --}}
    <div class="map-overlay absolute inset-0 z-10"></div>

    <iframe width="100%" height="500" style="border:0;" loading="lazy" allowfullscreen
        src="https://www.google.com/maps?q={{ urlencode($endereco) }}&output=embed">
    </iframe>
</div>

<style>
    .map-overlay {
        background: transparent;
        cursor: pointer;
    }
    .map-overlay.active {
        display: none; /* overlay some quando clicar/tocar */
    }
</style>

<script>
    const overlay = document.querySelector('.map-overlay');
    overlay.addEventListener('click', () => {
        overlay.classList.add('active');
    });
</script>

    {{-- Estilos personalizados --}}
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

</x-filament::page>
