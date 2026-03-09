<div>
    <div class="space-y-3"
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

        <div
            class="flex flex-col gap-6 rounded-2xl border border-dashed px-6 py-8 text-center transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-900/50"
            :class="{
                'border-blue-500 bg-blue-50 dark:bg-blue-900/10 dark:border-blue-400': isDropping,
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
            <!-- Empty State when NO images exist and NOT uploading -->
            @if(count($images) === 0)
            <div class="flex flex-col items-center justify-center gap-4 cursor-pointer" x-show="!uploading">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 shadow-sm dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-15 12.75h13.5A1.5 1.5 0 0 0 20.25 18V6a1.5 1.5 0 0 0-1.5-1.5H5.25A1.5 1.5 0 0 0 3.75 6v12a1.5 1.5 0 0 0 1.5 1.5Zm5.25-3.75h4.5a1.5 1.5 0 0 0 1.29-2.295l-2.25-3.75a1.5 1.5 0 0 0-2.58 0l-2.25 3.75A1.5 1.5 0 0 0 9 15.75Z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Haz clic o arrastra para subir fotos</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Formatos permitidos: JPG y PNG</p>
            </div>
            @endif

            <!-- Loading indicator for Livewire uploads -->
            <div x-show="uploading" class="flex flex-col items-center justify-center gap-4 py-6" style="display: none;">
                <svg class="h-8 w-8 animate-spin text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Subiendo imágenes... <span x-text="progress + '%'"></span></p>
            </div>

            <!-- Previews Wrapper -->
            @if(count($images) > 0)
            <div class="w-full space-y-3 text-left" x-show="!uploading" x-on:click.stop>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Arrastra las fotos para cambiar el orden. La primera será la portada. Sube más arrastrando encima el archivo.</p>
                    <button type="button" class="inline-flex items-center gap-2 self-center rounded-full bg-blue-100 px-4 py-1.5 text-xs font-semibold text-blue-700 transition duration-200 hover:bg-blue-200 focus-visible:outline-none" x-on:click.prevent="$refs.fileInput.click()">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4v9m0 0 3-3m-3 3-3-3"/></svg>
                        <span>Agregar más</span>
                    </button>
                </div>
                
                <div
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    wire:sortable="updateImageOrder"
                    wire:sortable.options="{ animation: 150 }"
                >
                    @foreach($images as $index => $imagen)
                        @php
                            // Safely extract the image URL for preview since it arrives as an array.
                            $url = $imagen['url'] ?? '';
                            if (!$url && isset($imagen['path']) && isset($imagen['disk'])) {
                                $url = \Illuminate\Support\Facades\Storage::disk($imagen['disk'])->url($imagen['path']);
                            }
                        @endphp
                        <div
                            wire:key="image-{{ $imagen['id'] }}"
                            wire:sortable.item="{{ $imagen['id'] }}"
                            class="group relative flex cursor-grab flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition duration-200 hover:border-blue-400 dark:border-slate-600 dark:bg-slate-800 dark:hover:border-blue-500"
                        >
                            <div class="absolute right-2 top-2 z-20 flex items-center gap-2">
                                @if($index === 0)
                                <span class="rounded-full bg-blue-600 px-2 py-0.5 text-[10px] font-semibold text-white shadow-sm">
                                    Portada
                                </span>
                                @endif
                                <button
                                    type="button"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-800 text-sm text-red-200 shadow-sm transition duration-150 hover:bg-slate-700 hover:text-red-100 focus-visible:outline-none dark:bg-slate-900"
                                    aria-label="Eliminar imagen"
                                    wire:click="deleteImage({{ $imagen['id'] }})"
                                    wire:confirm="¿Deseas eliminar esta imagen de forma permanente?"
                                >
                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 8 8m0-8-8 8" />
                                    </svg>
                                </button>
                            </div>

                            <div class="relative flex h-32 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-sm text-slate-400 dark:bg-slate-900">
                                <img
                                    src="{{ $url }}"
                                    class="h-full w-full object-cover pointer-events-none"
                                >
                                @if(!empty($watermarkPreviewUrl))
                                <img
                                    src="{{ $watermarkPreviewUrl }}"
                                    class="pointer-events-none absolute inset-0 h-full w-full select-none object-contain opacity-20"
                                >
                                @endif
                                
                                <div wire:sortable.handle class="absolute inset-0 z-10 hidden group-hover:block m-4">
                                    <div class="bg-black/40 backdrop-blur w-full h-full flex items-center justify-center rounded-lg opacity-0 hover:opacity-100 transition duration-200" title="Arrastra para reordenar">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 15h8M12 5v14"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @error('photos.*') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
        @error('photos') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
    </div>
</div>
