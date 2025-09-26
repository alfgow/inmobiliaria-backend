import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", () => {
    const ctx = document.getElementById("chartDashboard");
    if (ctx) {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun"],
                datasets: [
                    {
                        label: "Interesados",
                        data: [5, 10, 8, 15, 7, 12],
                        backgroundColor: "rgba(99, 102, 241, 0.6)",
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
            },
        });
    }

    document.querySelectorAll("[data-searchable-select]").forEach((container) => {
        const searchInput = container.querySelector("[data-search-input]");
        const select = container.querySelector("select");

        if (!searchInput || !select) {
            return;
        }

        const options = Array.from(select.options);

        const filterOptions = () => {
            const term = searchInput.value.trim().toLowerCase();

            options.forEach((option) => {
                if (option.value === "") {
                    option.hidden = false;
                    return;
                }

                const searchSource = option.dataset.searchable || option.textContent || "";
                const matches = term === "" || searchSource.toLowerCase().includes(term);

                option.hidden = !matches;
            });

            const selectedOption = select.selectedOptions[0];
            if (selectedOption && selectedOption.hidden) {
                select.value = "";
            }
        };

        searchInput.addEventListener("input", filterOptions);

        searchInput.addEventListener("search", filterOptions);

        searchInput.addEventListener("keydown", (event) => {
            if (event.key !== "Enter") {
                return;
            }

            event.preventDefault();

            const firstVisibleOption = options.find((option) => !option.hidden && option.value !== "");

            if (firstVisibleOption) {
                select.value = firstVisibleOption.value;
                select.dispatchEvent(new Event("change", { bubbles: true }));
            }
        });

        searchInput.addEventListener("blur", () => {
            if (searchInput.value.trim() === "") {
                options.forEach((option) => {
                    option.hidden = false;
                });
            }
        });
    });

    document.querySelectorAll("form[data-swal-loader]").forEach((form) => {
        form.addEventListener("submit", () => {
            if (!window.Swal || form.dataset.submitting === "true") {
                return;
            }

            form.dataset.submitting = "true";

            window.Swal.fire({
                title: "Registrando contacto",
                text: "Estamos guardando la información...",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    window.Swal.showLoading();
                },
            });
        });
    });

    document.querySelectorAll("form[data-swal-confirm]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (form.dataset.swalConfirmed === "true") {
                return;
            }

            const title = form.dataset.swalTitle || "¿Estás seguro?";
            const text = form.dataset.swalConfirm || "Esta acción no se puede deshacer.";
            const confirmButtonText =
                form.dataset.swalConfirmButton || "Sí, continuar";
            const cancelButtonText = form.dataset.swalCancelButton || "Cancelar";

            if (!window.Swal) {
                const shouldSubmit = window.confirm(`${title}\n\n${text}`);

                if (!shouldSubmit) {
                    event.preventDefault();
                }

                return;
            }

            event.preventDefault();

            window.Swal.fire({
                icon: "warning",
                title,
                text,
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText,
                reverseButtons: true,
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                form.dataset.swalConfirmed = "true";
                form.submit();
            });
        });
    });

    const sidebar = document.querySelector("[data-sidebar]");

    if (sidebar) {
        const openButtons = document.querySelectorAll("[data-sidebar-open]");
        const closeButtons = document.querySelectorAll("[data-sidebar-close]");
        const backdrop = document.querySelector("[data-sidebar-backdrop]");
        const sidebarLinks = sidebar.querySelectorAll("[data-sidebar-link]");
        const breakpoint = window.matchMedia("(min-width: 768px)");

        const hideBackdrop = () => {
            if (!backdrop) {
                return;
            }

            backdrop.classList.add("opacity-0", "pointer-events-none");
            backdrop.classList.remove("opacity-100");
        };

        const showBackdrop = () => {
            if (!backdrop) {
                return;
            }

            backdrop.classList.add("opacity-100");
            backdrop.classList.remove("opacity-0", "pointer-events-none");
        };

        const openSidebar = () => {
            sidebar.classList.remove("-translate-x-full");
            sidebar.classList.add("translate-x-0");
            sidebar.dataset.sidebarOpen = "true";

            if (!breakpoint.matches) {
                document.body.classList.add("overflow-hidden");
                showBackdrop();
            }
        };

        const closeSidebar = () => {
            sidebar.classList.remove("translate-x-0");
            sidebar.dataset.sidebarOpen = "false";

            if (breakpoint.matches) {
                sidebar.classList.remove("-translate-x-full");
            } else {
                sidebar.classList.add("-translate-x-full");
                document.body.classList.remove("overflow-hidden");
                hideBackdrop();
            }
        };

        const syncSidebarWithBreakpoint = (event) => {
            if (event.matches) {
                sidebar.classList.remove("-translate-x-full", "translate-x-0");
                sidebar.dataset.sidebarOpen = "false";
                document.body.classList.remove("overflow-hidden");
                hideBackdrop();
            } else if (sidebar.dataset.sidebarOpen === "true") {
                openSidebar();
            } else {
                sidebar.classList.add("-translate-x-full");
                sidebar.classList.remove("translate-x-0");
                hideBackdrop();
            }
        };

        syncSidebarWithBreakpoint(breakpoint);
        breakpoint.addEventListener("change", syncSidebarWithBreakpoint);

        openButtons.forEach((button) => {
            button.addEventListener("click", () => {
                openSidebar();
            });
        });

        closeButtons.forEach((button) => {
            button.addEventListener("click", () => {
                closeSidebar();
            });
        });

        sidebarLinks.forEach((link) => {
            link.addEventListener("click", () => {
                if (!breakpoint.matches) {
                    closeSidebar();
                }
            });
        });

        if (backdrop) {
            backdrop.addEventListener("click", () => {
                closeSidebar();
            });
        }

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && sidebar.dataset.sidebarOpen === "true") {
                closeSidebar();
            }
        });
    }

    const galleryInput = document.querySelector("[data-gallery-input]");
    const previewsContainer = document.querySelector("[data-gallery-previews-container]");

    if (galleryInput && previewsContainer) {
        const template = previewsContainer.querySelector("template[data-gallery-preview-template]");
        const watermarkUrl = previewsContainer.dataset.galleryWatermarkUrl || "";
        const dropzone = document.querySelector("[data-gallery-dropzone]");
        const counterElement = document.querySelector("[data-gallery-counter]");
        const MAX_FILES = 10;
        const canManageFiles =
            typeof window !== "undefined" && typeof window.DataTransfer !== "undefined";

        let selectedFiles = [];
        let dragSourceIndex = null;
        let dragSourcePreview = null;
        let dragInitiatedByHandle = false;

        const setDropzoneActive = (isActive) => {
            if (!dropzone) {
                return;
            }

            dropzone.classList.toggle("border-indigo-400/70", isActive);
            dropzone.classList.toggle("bg-gray-850/80", isActive);
            dropzone.classList.toggle("shadow-lg", isActive);
            dropzone.classList.toggle("shadow-indigo-500/30", isActive);
        };

        const isFileDragEvent = (event) => {
            if (!event || !event.dataTransfer) {
                return false;
            }

            const { types } = event.dataTransfer;

            if (!types) {
                return true;
            }

            return Array.from(types).includes("Files");
        };

        const updateFileCount = () => {
            if (!counterElement) {
                return;
            }

            counterElement.textContent = `${selectedFiles.length} de ${MAX_FILES} imágenes seleccionadas`;
            counterElement.classList.toggle("text-red-300", selectedFiles.length >= MAX_FILES);
            counterElement.classList.toggle("text-gray-400", selectedFiles.length < MAX_FILES);
        };

        const createPreviewElement = () => {
            if (!(template instanceof HTMLTemplateElement)) {
                throw new Error("Template de galería no disponible");
            }

            const fragment = template.content.cloneNode(true);
            const element = fragment.firstElementChild;

            if (!element) {
                throw new Error("No se pudo crear el contenedor de la vista previa");
            }

            return element;
        };

        const updateContainerVisibility = () => {
            previewsContainer.classList.toggle("hidden", selectedFiles.length === 0);
            updateFileCount();
        };

        const updateFileInput = () => {
            if (!canManageFiles) {
                return;
            }

            const dataTransfer = new DataTransfer();

            selectedFiles.forEach((file) => {
                dataTransfer.items.add(file);
            });

            galleryInput.files = dataTransfer.files;
        };

        const renderPreviews = () => {
            previewsContainer.querySelectorAll("[data-gallery-preview]").forEach((element) => {
                element.remove();
            });

            selectedFiles.forEach((file, index) => {
                const element = createPreviewElement();
                element.dataset.galleryPreview = "";
                element.dataset.galleryIndex = String(index);
                element.draggable = true;

                const loadingIndicator = element.querySelector("[data-gallery-loading]");
                const imgBase = element.querySelector("[data-gallery-preview-image]");
                const imgWater = element.querySelector("[data-gallery-preview-watermark]");
                const errorEl = element.querySelector("[data-gallery-error]");
                const coverBadge = element.querySelector("[data-gallery-cover-badge]");
                const filenameEl = element.querySelector("[data-gallery-filename]");

                if (coverBadge) {
                    coverBadge.classList.toggle("hidden", index !== 0);
                }

                if (filenameEl) {
                    filenameEl.textContent = file.name || `Imagen ${index + 1}`;
                }

                previewsContainer.appendChild(element);

                if (!imgBase) {
                    return;
                }

                const fileUrl = URL.createObjectURL(file);

                const hideLoading = () => {
                    if (loadingIndicator) {
                        loadingIndicator.classList.add("hidden");
                    }
                };

                imgBase.addEventListener(
                    "load",
                    () => {
                        hideLoading();
                        imgBase.classList.remove("hidden");

                        if (imgWater && watermarkUrl) {
                            imgWater.src = watermarkUrl;
                            imgWater.classList.remove("hidden");
                        }

                        URL.revokeObjectURL(fileUrl);
                    },
                    { once: true }
                );

                imgBase.addEventListener(
                    "error",
                    () => {
                        hideLoading();

                        if (errorEl) {
                            errorEl.textContent = "No se pudo leer la imagen";
                            errorEl.classList.remove("hidden");
                        }

                        URL.revokeObjectURL(fileUrl);
                    },
                    { once: true }
                );

                imgBase.src = fileUrl;
            });

            updateFileInput();
            updateContainerVisibility();
        };

        const addFilesToSelection = (files) => {
            if (!Array.isArray(files) || files.length === 0) {
                return;
            }

            const validFiles = files.filter((file) => file.type.startsWith("image/"));

            if (validFiles.length === 0) {
                return;
            }

            const availableSlots = MAX_FILES - selectedFiles.length;

            if (availableSlots <= 0) {
                return;
            }

            const filesToAdd = validFiles.slice(0, availableSlots);

            selectedFiles = selectedFiles.concat(filesToAdd);
            renderPreviews();
        };

        const findPreviewElement = (target) => {
            if (!(target instanceof Element)) {
                return null;
            }

            return target.closest("[data-gallery-preview]");
        };

        if (dropzone) {
            const handleFileDrop = (event) => {
                if (!isFileDragEvent(event)) {
                    return;
                }

                event.preventDefault();
                setDropzoneActive(false);

                const files = Array.from(event.dataTransfer?.files || []);

                addFilesToSelection(files);
            };

            ["dragenter", "dragover"].forEach((type) => {
                dropzone.addEventListener(type, (event) => {
                    if (!isFileDragEvent(event)) {
                        return;
                    }

                    event.preventDefault();
                    setDropzoneActive(true);
                });
            });

            dropzone.addEventListener("dragleave", (event) => {
                if (!isFileDragEvent(event)) {
                    return;
                }

                const related = event.relatedTarget;

                if (related instanceof Element && dropzone.contains(related)) {
                    return;
                }

                setDropzoneActive(false);
            });

            dropzone.addEventListener("drop", handleFileDrop);

            dropzone.addEventListener("click", () => {
                galleryInput.click();
            });

            dropzone.addEventListener("keydown", (event) => {
                if (event.key !== "Enter" && event.key !== " ") {
                    return;
                }

                event.preventDefault();
                galleryInput.click();
            });
        }

        galleryInput.addEventListener("change", () => {
            const files = Array.from(galleryInput.files || []);

            addFilesToSelection(files);

            if (canManageFiles) {
                galleryInput.value = "";
            }
        });

        previewsContainer.addEventListener("mousedown", (event) => {
            dragInitiatedByHandle = event.target instanceof Element
                && Boolean(event.target.closest("[data-gallery-drag-handle]"));
        });

        previewsContainer.addEventListener("mouseup", () => {
            dragInitiatedByHandle = false;
        });

        previewsContainer.addEventListener("mouseleave", (event) => {
            if (event.buttons === 0) {
                dragInitiatedByHandle = false;
            }
        });

        previewsContainer.addEventListener("click", (event) => {
            const removeButton = event.target instanceof Element
                ? event.target.closest("[data-gallery-remove]")
                : null;

            if (!removeButton) {
                return;
            }

            event.preventDefault();

            const preview = findPreviewElement(removeButton);

            if (!preview) {
                return;
            }

            const index = Number(preview.dataset.galleryIndex);

            if (Number.isNaN(index)) {
                return;
            }

            selectedFiles.splice(index, 1);
            renderPreviews();
        });

        previewsContainer.addEventListener("dragstart", (event) => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            if (target.closest("[data-gallery-remove]")) {
                event.preventDefault();
                return;
            }

            if (!dragInitiatedByHandle) {
                event.preventDefault();
                return;
            }

            const preview = findPreviewElement(target);

            if (!preview) {
                return;
            }

            const index = Number(preview.dataset.galleryIndex);

            if (Number.isNaN(index)) {
                return;
            }

            dragSourceIndex = index;
            dragSourcePreview = preview;

            dragInitiatedByHandle = false;

            preview.classList.add("opacity-50");

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = "move";
                event.dataTransfer.setData("text/plain", String(index));
            }
        });

        previewsContainer.addEventListener("dragend", () => {
            if (dragSourcePreview) {
                dragSourcePreview.classList.remove("opacity-50");
            }

            dragSourceIndex = null;
            dragSourcePreview = null;
            dragInitiatedByHandle = false;
        });

        previewsContainer.addEventListener("dragover", (event) => {
            if (isFileDragEvent(event)) {
                event.preventDefault();
                setDropzoneActive(true);
                return;
            }

            if (dragSourceIndex === null) {
                return;
            }

            event.preventDefault();

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = "move";
            }
        });

        previewsContainer.addEventListener("drop", (event) => {
            if (isFileDragEvent(event)) {
                event.preventDefault();
                setDropzoneActive(false);

                const files = Array.from(event.dataTransfer?.files || []);

                addFilesToSelection(files);
                return;
            }

            if (dragSourceIndex === null) {
                return;
            }

            event.preventDefault();

            const preview = findPreviewElement(event.target);

            let destinationIndex = selectedFiles.length;

            if (preview) {
                const targetIndex = Number(preview.dataset.galleryIndex);

                if (!Number.isNaN(targetIndex)) {
                    destinationIndex = targetIndex;

                    const rect = preview.getBoundingClientRect();
                    const shouldPlaceAfter = event.clientY > rect.top + rect.height / 2;

                    if (shouldPlaceAfter) {
                        destinationIndex += 1;
                    }
                }
            }

            const [movedFile] = selectedFiles.splice(dragSourceIndex, 1);

            if (!movedFile) {
                dragSourceIndex = null;
                dragSourcePreview = null;
                return;
            }

            if (destinationIndex > selectedFiles.length) {
                destinationIndex = selectedFiles.length;
            }

            if (dragSourceIndex < destinationIndex) {
                destinationIndex -= 1;
            }

            selectedFiles.splice(destinationIndex, 0, movedFile);

            if (dragSourcePreview) {
                dragSourcePreview.classList.remove("opacity-50");
            }

            dragSourceIndex = null;
            dragSourcePreview = null;

            renderPreviews();
        });

        previewsContainer.addEventListener("dragleave", (event) => {
            if (!isFileDragEvent(event)) {
                return;
            }

            const related = event.relatedTarget;

            if (related instanceof Element
                && (related.closest("[data-gallery-preview]") || dropzone?.contains(related))) {
                return;
            }

            setDropzoneActive(false);
        });

        updateContainerVisibility();
    }
});
