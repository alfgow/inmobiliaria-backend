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
        const watermarkUrl = previewsContainer.dataset.galleryWatermarkUrl ?? "";
        let watermarkPromise;

        const loadWatermark = () => {
            if (!watermarkUrl) {
                return Promise.resolve(null);
            }

            if (!watermarkPromise) {
                watermarkPromise = new Promise((resolve, reject) => {
                    const watermark = new Image();
                    watermark.crossOrigin = "anonymous";
                    watermark.onload = () => resolve(watermark);
                    watermark.onerror = () => {
                        watermarkPromise = undefined;
                        reject(new Error("No se pudo cargar la marca de agua"));
                    };
                    watermark.src = watermarkUrl;
                });
            }

            return watermarkPromise;
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

        const clearPreviews = () => {
            previewsContainer.querySelectorAll("[data-gallery-preview]").forEach((element) => {
                element.remove();
            });
        };

        const updateContainerVisibility = () => {
            const hasPreviews = previewsContainer.querySelectorAll("[data-gallery-preview]").length > 0;

            previewsContainer.classList.toggle("hidden", !hasPreviews);
        };

        const renderPreview = async (file) => {
            const element = createPreviewElement();
            element.dataset.galleryPreview = "";

            const loadingIndicator = element.querySelector("[data-gallery-loading]");
            const imageElement = element.querySelector("[data-gallery-preview-image]");
            const errorElement = element.querySelector("[data-gallery-error]");

            previewsContainer.appendChild(element);
            updateContainerVisibility();

            const fileUrl = URL.createObjectURL(file);
            const baseImage = new Image();

            const cleanup = () => {
                URL.revokeObjectURL(fileUrl);
            };

            try {
                const baseImageLoad = new Promise((resolve, reject) => {
                    baseImage.onload = resolve;
                    baseImage.onerror = () => reject(new Error("No se pudo leer la imagen"));
                });
                const watermarkLoad = loadWatermark().catch((error) => {
                    console.warn(error);

                    return null;
                });

                baseImage.src = fileUrl;

                await baseImageLoad;
                const watermark = await watermarkLoad;

                const maxDimension = 1200;
                const scale = Math.min(1, maxDimension / Math.max(baseImage.width, baseImage.height));
                const canvasWidth = Math.round(baseImage.width * scale);
                const canvasHeight = Math.round(baseImage.height * scale);

                const canvas = document.createElement("canvas");
                canvas.width = canvasWidth;
                canvas.height = canvasHeight;

                const context = canvas.getContext("2d");

                if (!context) {
                    throw new Error("No se pudo preparar el lienzo");
                }

                context.drawImage(baseImage, 0, 0, canvasWidth, canvasHeight);

                if (watermark) {
                    const watermarkWidth = watermark.width || 1;
                    const watermarkHeight = watermark.height || 1;
                    const watermarkRatio = watermarkWidth / watermarkHeight;
                    const canvasRatio = canvasWidth / canvasHeight;

                    let sourceWidth = watermarkWidth;
                    let sourceHeight = watermarkHeight;
                    let sourceX = 0;
                    let sourceY = 0;

                    if (watermarkRatio > canvasRatio) {
                        sourceHeight = watermarkHeight;
                        sourceWidth = sourceHeight * canvasRatio;
                        sourceX = (watermarkWidth - sourceWidth) / 2;
                    } else {
                        sourceWidth = watermarkWidth;
                        sourceHeight = sourceWidth / canvasRatio;
                        sourceY = (watermarkHeight - sourceHeight) / 2;
                    }

                    context.save();
                    context.globalAlpha = 0.25;
                    context.drawImage(
                        watermark,
                        sourceX,
                        sourceY,
                        sourceWidth,
                        sourceHeight,
                        0,
                        0,
                        canvasWidth,
                        canvasHeight,
                    );
                    context.restore();
                }

                const previewUrl = canvas.toDataURL("image/jpeg", 0.92);

                if (loadingIndicator) {
                    loadingIndicator.classList.add("hidden");
                }

                if (imageElement) {
                    imageElement.src = previewUrl;
                    imageElement.classList.remove("hidden");
                }
            } catch (error) {
                if (loadingIndicator) {
                    loadingIndicator.classList.add("hidden");
                }

                if (errorElement) {
                    errorElement.textContent = error instanceof Error ? error.message : "Error desconocido al generar la vista previa";
                    errorElement.classList.remove("hidden");
                }
            } finally {
                cleanup();
            }
        };

        galleryInput.addEventListener("change", () => {
            clearPreviews();

            const files = Array.from(galleryInput.files || []).filter((file) => file.type.startsWith("image/"));

            files.slice(0, 10).forEach((file) => {
                void renderPreview(file);
            });

            updateContainerVisibility();
        });

        updateContainerVisibility();
    }
});
