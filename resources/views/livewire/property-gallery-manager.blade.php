<div>
    <div class="space-y-4"
        x-data="{
            isDropping: false,
            uploading: false,
            progress: 0,
            handleDrop(event) {
                this.isDropping = false;
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    this.$refs.fileInput.files = files;
                    this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false; progress = 0"
        x-on:livewire-upload-error="uploading = false; progress = 0"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <input
            type="file"
            wire:model.live="photos"
            accept="image/*"
            multiple
            class="hidden"
            x-ref="fileInput"
        >

        {{-- Drop Zone --}}
        <div
            class="relative flex flex-col items-center justify-center gap-4 overflow-hidden rounded-xl border-2 border-dashed px-6 py-10 text-center transition-all duration-200"
            :class="{
                'border-blue-500 bg-blue-50/50 shadow-inner dark:bg-blue-900/10': isDropping,
                'border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:hover:border-blue-500 dark:hover:bg-slate-800': !isDropping
            }"
            x-on:drop.prevent="handleDrop"
            x-on:dragover.prevent="isDropping = true"
            x-on:dragleave.prevent="isDropping = false"
            x-on:click="$refs.fileInput.click()"
            role="button"
            tabindex="0"
            aria-label="Agregar imágenes a la galería"
        >
            {{-- Empty State --}}
            @if(count($images) === 0)
            <div class="flex flex-col items-center justify-center gap-4" x-show="!uploading">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-200 dark:shadow-blue-900/30">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Arrastra fotos aquí o haz clic para seleccionar</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">JPG y PNG hasta 10MB por archivo</p>
                </div>
            </div>
            @endif

            {{-- Uploading State --}}
            <div x-show="uploading" class="flex flex-col items-center justify-center gap-4 py-6" style="display: none;">
                <div class="relative">
                    <svg class="h-12 w-12 animate-spin text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Subiendo imágenes...</p>
                    <p class="mt-1 text-xs text-slate-500"><span x-text="progress"></span>%</p>
                </div>
                {{-- Progress Bar --}}
                <div class="h-1.5 w-48 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>

            {{-- Add More Button (when images exist) --}}
            @if(count($images) > 0)
            <div class="flex flex-col items-center justify-center gap-3 py-4" x-show="!uploading">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Arrastra más fotos o haz clic para agregar</p>
            </div>
            @endif
        </div>

        {{-- Images Grid --}}
        @if(count($images) > 0)
        <div class="space-y-3" x-show="!uploading" x-on:click.stop>
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Arrastra para reordenar · La primera es la portada
                    </span>
                </p>
                <button 
                    type="button" 
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-100 px-4 py-1.5 text-xs font-semibold text-blue-700 transition-all duration-200 hover:bg-blue-200 hover:shadow-sm dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                    x-on:click.prevent="$refs.fileInput.click()"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Agregar más
                </button>
            </div>
            
            <div
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                wire:sortable="updateImageOrder"
                wire:sortable.options="{ animation: 150 }"
            >
                @foreach($images as $index => $imagen)
                    @php
                        $url = $imagen['url'] ?? '';
                        if (!$url && isset($imagen['path']) && isset($imagen['disk'])) {
                            $url = \Illuminate\Support\Facades\Storage::disk($imagen['disk'])->url($imagen['path']);
                        }
                    @endphp
                    <div
                        wire:key="image-{{ $imagen['id'] }}"
                        wire:sortable.item="{{ $imagen['id'] }}"
                        class="group relative flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:border-blue-400 hover:shadow-md dark:border-slate-600 dark:bg-slate-800 dark:hover:border-blue-500"
                    >
                        {{-- Badge: Cover --}}
                        @if($index === 0)
                        <div class="absolute left-2 top-2 z-10">
                            <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-md">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                Portada
                            </span>
                        </div>
                        @endif

                        {{-- Delete Button --}}
                        <button
                            type="button"
                            class="absolute right-2 top-2 z-10 inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-900/80 text-white shadow-sm backdrop-blur transition-all duration-200 hover:bg-red-600 hover:scale-110 focus:outline-none"
                            aria-label="Eliminar imagen"
                            wire:click="deleteImage({{ $imagen['id'] }})"
                            wire:confirm="¿Deseas eliminar esta imagen de forma permanente?"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        {{-- Image --}}
                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-900">
                            <img
                                src="{{ $url }}"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                alt="Imagen {{ $index + 1 }}"
                            >
                            @if(!empty($watermarkPreviewUrl))
                            <img
                                src="{{ $watermarkPreviewUrl }}"
                                class="pointer-events-none absolute inset-0 h-full w-full select-none object-contain opacity-20"
                                alt="Watermark"
                            >
                            @endif
                            
                            {{-- Drag Overlay --}}
                            <div wire:sortable.handle class="absolute inset-0 flex cursor-grab items-center justify-center opacity-0 transition-opacity duration-200 group-hover:opacity-100 active:cursor-grabbing">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-900/60 text-white shadow-lg backdrop-blur-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800/50">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">#{{ $index + 1 }}</span>
                            <span class="text-[10px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Arrastra para mover</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @error('photos.*') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
        @error('photos') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
    </div>
</div>
