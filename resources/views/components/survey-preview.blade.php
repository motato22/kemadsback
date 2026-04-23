<div class="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
    <div class="relative w-full max-w-4xl aspect-video bg-black rounded-[2rem] p-4 shadow-2xl border-8 border-gray-800 ring-4 ring-gray-900">
        <div class="absolute left-2 top-1/2 -translate-y-1/2 w-4 h-4 bg-gray-900 rounded-full border border-gray-700 shadow-inner"></div>

        <div class="w-full h-full bg-white dark:bg-gray-800 rounded-xl overflow-hidden flex flex-col relative">

            <div class="h-1 bg-gray-200 w-full absolute top-0 left-0 z-10">
                <div class="h-full bg-blue-500 w-full transition-all duration-1000"></div>
            </div>

            <div class="relative p-6 bg-[#002557] text-white text-center shadow-md">
                <h2 class="text-3xl font-bold" style="font-family: 'Montserrat', sans-serif;">{{ $survey->name }}</h2>
                <p class="text-sm mt-1 text-[#6CCAFF]">
                    {{ $survey->type === 'trivia' ? '¡Demuestra lo que sabes y gana un premio!' : 'Tu opinión es muy importante para nosotros' }}
                </p>
                <p class="absolute top-6 right-6 text-xs text-gray-400">Timeout: {{ $survey->timeout_seconds }}s</p>
            </div>

            <div class="flex-1 overflow-y-auto p-8 space-y-8 bg-gray-100 dark:bg-gray-800/50">
                @forelse($survey->questions as $index => $question)
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-600">
                        <h3 class="text-xl font-semibold mb-6 text-gray-800 dark:text-gray-100">
                            {{ $index + 1 }}. {{ $question->question_text }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($question->options as $option)
                                <div class="px-4 py-4 text-left rounded-lg border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex items-center justify-between">
                                    <span class="text-gray-700 dark:text-gray-200 font-medium">{{ $option->option_text }}</span>

                                    @if($survey->type === 'trivia' && $option->is_correct)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                            ✓ Correcta
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-200">Sin preguntas</h3>
                        <p class="mt-1 text-sm text-gray-500">Agrega preguntas a la encuesta para verlas aquí.</p>
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                <div class="w-1/2">
                    <input type="email" placeholder="Ingresa tu correo para finalizar..." class="w-full px-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white bg-gray-100 cursor-not-allowed" disabled>
                </div>
                <button class="px-8 py-3 bg-[#2370D8] text-white font-bold rounded-lg cursor-not-allowed opacity-80" disabled>
                    Enviar Respuestas
                </button>
            </div>
        </div>
    </div>
</div>
