import Chart from "chart.js/auto";
import Choices from "choices.js";
import "choices.js/styles.css";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import "leaflet-control-geocoder/dist/Control.Geocoder.css";
import "leaflet-control-geocoder";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

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

    const choicesInstances = new Map();

    const initializeInmuebleMap = () => {
        const direccionInput = document.querySelector("#direccion");
        const mapContainer = document.querySelector("#inmueble-map");

        if (!direccionInput || !mapContainer) {
            return;
        }

        if (mapContainer.__inmuebleMapInitialized) {
            return;
        }

        mapContainer.__inmuebleMapInitialized = true;

        const latInput = document.querySelector('input[name="latitud"]');
        const lngInput = document.querySelector('input[name="longitud"]');
        const resolveUrl = mapContainer.dataset.postalResolveUrl || "";

        const parseCoordinate = (value) => {
            const numericValue = parseFloat(value);

            return Number.isFinite(numericValue) ? numericValue : null;
        };

        const defaultCenter = [19.432608, -99.133209];
        const initialLat = parseCoordinate(latInput?.value);
        const initialLng = parseCoordinate(lngInput?.value);
        const hasInitialCoordinates =
            initialLat !== null && initialLng !== null;
        const startingPoint = hasInitialCoordinates
            ? [initialLat, initialLng]
            : defaultCenter;

        const map = L.map(mapContainer, {
            attributionControl: true,
            zoomControl: true,
        });

        map.setView(startingPoint, hasInitialCoordinates ? 16 : 13);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
            maxZoom: 19,
        }).addTo(map);

        const marker = L.marker(startingPoint, { draggable: false }).addTo(map);

        const formatCoordinate = (coordinate) => {
            return Number.isFinite(coordinate)
                ? coordinate.toFixed(6)
                : "";
        };

        const updateCoordinateInputs = (latlng) => {
            if (!latlng) {
                return;
            }

            if (latInput) {
                latInput.value = formatCoordinate(latlng.lat);
            }

            if (lngInput) {
                lngInput.value = formatCoordinate(latlng.lng);
            }
        };

        if (hasInitialCoordinates) {
            updateCoordinateInputs({ lat: initialLat, lng: initialLng });
        }

        const pickFirst = (...values) => {
            for (const value of values) {
                if (value === undefined || value === null) {
                    continue;
                }

                const normalized = String(value).trim();

                if (normalized !== "") {
                    return normalized;
                }
            }

            return "";
        };

        const extractAddressDetails = (result) => {
            const address =
                result?.properties?.address ||
                result?.properties?.raw?.address ||
                result?.address ||
                {};

            return {
                codigo_postal: pickFirst(
                    address.postcode,
                    address.postalcode,
                    address["postal-code"],
                    address["postal_code"]
                ),
                colonia: pickFirst(
                    address.neighbourhood,
                    address.suburb,
                    address.quarter,
                    address.village,
                    address.hamlet,
                    address.residential
                ),
                municipio: pickFirst(
                    address.city,
                    address.town,
                    address.municipality,
                    address.county,
                    address["state_district"],
                    address.region
                ),
                estado: pickFirst(address.state, address["state_name"], address.region),
            };
        };

        const selectIds = [
            "codigo_postal",
            "colonia",
            "municipio",
            "estado",
        ];

        const setSelectValue = (select, value) => {
            if (!select) {
                return;
            }

            const normalized = typeof value === "string" ? value.trim() : "";
            const previousValue = select.value;

            if (normalized) {
                let option = Array.from(select.options).find((item) => {
                    return item.value === normalized;
                });

                if (!option) {
                    option = new Option(normalized, normalized, true, true);
                    select.add(option);
                } else {
                    option.selected = true;
                }

                select.value = normalized;
            } else {
                select.value = "";

                const placeholder = select.querySelector("option[value='']");

                if (placeholder) {
                    placeholder.selected = true;
                }
            }

            const choicesInstance = choicesInstances.get(select);

            if (choicesInstance) {
                try {
                    choicesInstance.removeActiveItems();

                    if (normalized) {
                        choicesInstance.setChoiceByValue(normalized);
                    } else {
                        const placeholder = select.querySelector("option[value='']");

                        if (placeholder) {
                            choicesInstance.setChoiceByValue(placeholder.value);
                        }
                    }
                } catch (error) {
                    console.warn(
                        "No fue posible sincronizar la selección con Choices.js",
                        error
                    );
                }
            }

            if (previousValue !== select.value) {
                select.dispatchEvent(new Event("change", { bubbles: true }));
            }
        };

        const updatePostalSelects = (details) => {
            if (!details) {
                return;
            }

            selectIds.forEach((id) => {
                const select = document.getElementById(id);

                setSelectValue(select, details[id]);
            });
        };

        const resolvePostalInformation = async (addressDetails) => {
            if (!addressDetails) {
                return;
            }

            const typePriority = [
                "codigo_postal",
                "colonia",
                "municipio",
                "estado",
            ];
            const selectedType = typePriority.find((key) => {
                return Boolean(addressDetails[key]);
            });

            if (!resolveUrl || !selectedType) {
                updatePostalSelects(addressDetails);
                return;
            }

            const params = new URLSearchParams({
                type: selectedType,
                value: addressDetails[selectedType],
            });

            typePriority.forEach((key) => {
                if (!addressDetails[key]) {
                    return;
                }

                params.set(key, addressDetails[key]);
            });

            try {
                const response = await fetch(
                    `${resolveUrl}?${params.toString()}`,
                    {
                        headers: { Accept: "application/json" },
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `No se pudo resolver la información postal (${response.status})`
                    );
                }

                const payload = await response.json();
                const results =
                    payload && Array.isArray(payload.data) ? payload.data : [];

                if (results.length > 0) {
                    updatePostalSelects(results[0]);
                    return;
                }
            } catch (error) {
                console.error(
                    "No fue posible obtener la información postal desde el geocodificador.",
                    error
                );
            }

            updatePostalSelects(addressDetails);
        };

        const geocoderFactory = L.Control?.Geocoder?.nominatim
            ? L.Control.Geocoder.nominatim
            : null;

        if (!geocoderFactory) {
            console.warn(
                "Leaflet Control Geocoder no está disponible; no se inicializará el mapa del inmueble."
            );
            return;
        }

        const geocoder = geocoderFactory();
        let activeGeocodeToken = 0;

        const handleGeocodeResults = async (results) => {
            if (!results || results.length === 0) {
                return;
            }

            const [firstResult] = results;
            const latlng =
                firstResult?.center ||
                firstResult?.latlng ||
                firstResult?.location ||
                null;

            if (latlng) {
                marker.setLatLng(latlng);
                map.setView(latlng, Math.max(map.getZoom(), 16), {
                    animate: true,
                });
                updateCoordinateInputs(latlng);
            }

            const addressDetails = extractAddressDetails(firstResult);

            if (addressDetails) {
                await resolvePostalInformation(addressDetails);
            }
        };

        const debounce = (fn, delay = 600) => {
            let timeoutId;

            return (...args) => {
                if (timeoutId) {
                    window.clearTimeout(timeoutId);
                }

                timeoutId = window.setTimeout(() => {
                    fn(...args);
                }, delay);
            };
        };

        const geocodeDireccion = (query) => {
            const trimmed = (query || "").trim();

            if (trimmed.length < 3) {
                return;
            }

            const requestToken = ++activeGeocodeToken;

            geocoder.geocode(trimmed, (results) => {
                if (requestToken !== activeGeocodeToken) {
                    return;
                }

                handleGeocodeResults(results);
            });
        };

        const debouncedGeocode = debounce((eventOrValue) => {
            const value =
                typeof eventOrValue === "string"
                    ? eventOrValue
                    : eventOrValue?.target?.value;

            geocodeDireccion(value || "");
        });

        direccionInput.addEventListener("input", debouncedGeocode);
        direccionInput.addEventListener("change", debouncedGeocode);

        if (!hasInitialCoordinates && direccionInput.value) {
            geocodeDireccion(direccionInput.value);
        }
    };

    const initializeChoicesSelect = (select) => {
        if (!select) {
            return null;
        }

        if (select.__choicesInstance) {
            choicesInstances.set(select, select.__choicesInstance);
            return select.__choicesInstance;
        }

        const container = select.closest("[data-searchable-select]");

        if (!container) {
            return null;
        }

        const searchPlaceholder =
            container.dataset.searchPlaceholder ||
            select.dataset.searchPlaceholder ||
            "Buscar...";

        const instance = new Choices(select, {
            searchPlaceholderValue: searchPlaceholder,
        });

        select.__choicesInstance = instance;
        container.dataset.choicesInitialized = "true";
        choicesInstances.set(select, instance);

        return instance;
    };

    document
        .querySelectorAll("[data-searchable-select] select")
        .forEach((select) => {
            initializeChoicesSelect(select);
        });

    const initializePostalSelector = (container) => {
        if (!container || container.__postalSelectorInitialized) {
            return;
        }

        const baseUrl = container.dataset.postalOptionsUrl;

        if (!baseUrl) {
            return;
        }

        const normalizedBaseUrl = baseUrl.replace(/\/$/, "");
        const resolveUrl = `${normalizedBaseUrl}/resolve`;
        const fieldOrder = [
            "codigo_postal",
            "colonia",
            "municipio",
            "estado",
        ];
        const fields = {};

        fieldOrder.forEach((key) => {
            const select =
                container.querySelector(`select[name="${key}"]`) ||
                container.querySelector(`#${key}`);

            if (!select) {
                return;
            }

            const placeholderOption = select.querySelector("option[value='']");
            const placeholderText = placeholderOption
                ? placeholderOption.textContent || ""
                : "Selecciona una opción";

            fields[key] = {
                select,
                placeholder: placeholderText,
                choices: initializeChoicesSelect(select),
            };
        });

        if (Object.keys(fields).length === 0) {
            return;
        }

        container.__postalSelectorInitialized = true;

        const uniqueValuesFromResults = (results, key) => {
            const values = [];

            results.forEach((item) => {
                if (!item || item[key] === undefined || item[key] === null) {
                    return;
                }

                const value = String(item[key]).trim();

                if (!value || values.includes(value)) {
                    return;
                }

                values.push(value);
            });

            return values;
        };

        const debounce = (fn, delay = 250) => {
            let timeoutId;

            return (...args) => {
                if (timeoutId) {
                    window.clearTimeout(timeoutId);
                }

                timeoutId = window.setTimeout(() => {
                    fn(...args);
                }, delay);
            };
        };

        let isUpdating = false;
        let activeRequestToken = 0;
        let activeOptionsRequestToken = 0;

        const fetchOptions = async (type, searchTerm = "") => {
            const params = new URLSearchParams({ type });
            const trimmedSearch = (searchTerm || "").trim();

            if (trimmedSearch !== "") {
                params.set("search", trimmedSearch);
            }

            fieldOrder.forEach((fieldKey) => {
                const field = fields[fieldKey];

                if (!field) {
                    return;
                }

                const value = field.select.value;

                if (value) {
                    params.set(fieldKey, value);
                }
            });

            const url = `${normalizedBaseUrl}?${params.toString()}`;
            const response = await fetch(url, {
                headers: { Accept: "application/json" },
            });

            if (!response.ok) {
                throw new Error(
                    `No se pudieron obtener opciones de códigos postales (${response.status})`
                );
            }

            const payload = await response.json();

            if (!payload || !Array.isArray(payload.data)) {
                return [];
            }

            return payload.data;
        };

        const repopulateField = (
            key,
            values,
            { triggered = false, autoSelectSingle = false } = {}
        ) => {
            const field = fields[key];

            if (!field) {
                return { changed: false, newValue: "" };
            }

            const { select, placeholder, choices } = field;
            const previousValue = select.value;
            const availableValues = Array.isArray(values) ? values : [];
            const normalizedValues = [];

            availableValues.forEach((value) => {
                if (value === null || value === undefined) {
                    return;
                }

                const normalized = String(value).trim();

                if (!normalized || normalizedValues.includes(normalized)) {
                    return;
                }

                normalizedValues.push(normalized);
            });

            let newValue = previousValue;

            if (!normalizedValues.includes(previousValue)) {
                if (autoSelectSingle && normalizedValues.length === 1) {
                    newValue = normalizedValues[0];
                } else {
                    newValue = "";
                }
            }

            if (triggered && previousValue && normalizedValues.includes(previousValue)) {
                newValue = previousValue;
            }

            const fragment = document.createDocumentFragment();
            const placeholderOption = document.createElement("option");

            placeholderOption.value = "";
            placeholderOption.textContent = placeholder || "Selecciona una opción";

            if (!newValue) {
                placeholderOption.selected = true;
            }

            fragment.appendChild(placeholderOption);

            normalizedValues.forEach((value) => {
                const option = document.createElement("option");

                option.value = value;
                option.textContent = value;
                option.dataset.searchable = value.toLowerCase();

                if (value === newValue) {
                    option.selected = true;
                }

                fragment.appendChild(option);
            });

            select.innerHTML = "";
            select.appendChild(fragment);
            select.value = newValue;

            if (choices) {
                choices.refresh({ resetSearch: true, dispatchChange: false });
            }

            const changed = previousValue !== newValue;

            if (changed && !triggered) {
                select.dispatchEvent(new Event("change", { bubbles: true }));
            }

            return { changed, newValue };
        };

        const applyResults = (results, triggeredKey) => {
            const safeResults = Array.isArray(results) ? results : [];

            isUpdating = true;

            try {
                fieldOrder.forEach((key) => {
                    if (!fields[key]) {
                        return;
                    }

                    const values = uniqueValuesFromResults(safeResults, key);
                    const autoSelectSingle = key !== triggeredKey;

                    repopulateField(key, values, {
                        triggered: key === triggeredKey,
                        autoSelectSingle,
                    });
                });
            } finally {
                isUpdating = false;
            }
        };

        const requestOptionsUpdate = async (type, searchTerm = "") => {
            const requestToken = ++activeOptionsRequestToken;

            try {
                const values = await fetchOptions(type, searchTerm);

                if (requestToken !== activeOptionsRequestToken) {
                    return;
                }

                repopulateField(type, values, { triggered: true });
            } catch (error) {
                console.error(
                    "No fue posible obtener las opciones de códigos postales.",
                    error
                );
            }
        };

        const fetchCombinations = async (type, value) => {
            const params = new URLSearchParams({ type, value });
            const url = `${resolveUrl}?${params.toString()}`;
            const response = await fetch(url, {
                headers: { Accept: "application/json" },
            });

            if (!response.ok) {
                throw new Error(
                    `No se pudo resolver la información postal (${response.status})`
                );
            }

            const payload = await response.json();

            if (!payload || !Array.isArray(payload.data)) {
                return [];
            }

            return payload.data;
        };

        const handleChange = async (type) => {
            if (isUpdating) {
                return;
            }

            const field = fields[type];

            if (!field) {
                return;
            }

            const value = field.select.value;

            if (!value) {
                const fallback = fieldOrder.find((key) => {
                    if (key === type || !fields[key]) {
                        return false;
                    }

                    const fallbackField = fields[key];
                    return Boolean(fallbackField.select.value);
                });

                if (fallback) {
                    await handleChange(fallback);
                } else {
                    applyResults([], type);
                }

                return;
            }

            const requestToken = ++activeRequestToken;

            try {
                const results = await fetchCombinations(type, value);

                if (requestToken !== activeRequestToken) {
                    return;
                }

                if (!results || results.length === 0) {
                    applyResults([], type);
                    return;
                }

                applyResults(results, type);
            } catch (error) {
                console.error(
                    "No fue posible actualizar la información del código postal.",
                    error
                );
            }
        };

        fieldOrder.forEach((key) => {
            if (!fields[key]) {
                return;
            }

            fields[key].select.addEventListener("change", () => {
                handleChange(key);
            });

            const { choices } = fields[key];

            if (choices && choices.searchInput) {
                const debouncedFetch = debounce((term) => {
                    requestOptionsUpdate(key, term);
                });

                const triggerFetch = () => {
                    const searchTerm = choices.searchInput.value || "";

                    debouncedFetch(searchTerm);
                };

                choices.searchInput.addEventListener("focus", triggerFetch);
                choices.searchInput.addEventListener("input", triggerFetch);
                choices.searchInput.addEventListener("search", triggerFetch);
                choices.searchInput.addEventListener("keydown", (event) => {
                    if (event.key !== "Enter") {
                        return;
                    }

                    event.preventDefault();
                    triggerFetch();
                });
            }
        });

        const initialField = fieldOrder.find((key) => {
            return fields[key] && fields[key].select.value;
        });

        if (initialField) {
            handleChange(initialField);
        }

        const firstAvailableField = fieldOrder.find((key) => {
            return fields[key];
        });

        if (firstAvailableField) {
            const initialSearchTerm = fields[firstAvailableField]?.choices?.searchInput
                ? fields[firstAvailableField].choices.searchInput.value || ""
                : "";

            requestOptionsUpdate(firstAvailableField, initialSearchTerm);
        }
    };

    document
        .querySelectorAll("[data-postal-selector]")
        .forEach((container) => {
            initializePostalSelector(container);
        });

    initializeInmuebleMap();

    document.querySelectorAll("form[data-swal-loader]").forEach((form) => {
        form.addEventListener("submit", () => {
            if (!window.Swal || form.dataset.submitting === "true") {
                return;
            }

            form.dataset.submitting = "true";

            const title = form.dataset.swalLoaderTitle || "Registrando contacto";
            const text = form.dataset.swalLoaderText || "Estamos guardando la información...";

            window.Swal.fire({
                title,
                text,
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
            const text =
                form.dataset.swalConfirm || "Esta acción no se puede deshacer.";
            const confirmButtonText =
                form.dataset.swalConfirmButton || "Sí, continuar";
            const cancelButtonText =
                form.dataset.swalCancelButton || "Cancelar";

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
            if (
                event.key === "Escape" &&
                sidebar.dataset.sidebarOpen === "true"
            ) {
                closeSidebar();
            }
        });
    }

    const galleryInput = document.querySelector("[data-gallery-input]");
    const previewsContainer = document.querySelector(
        "[data-gallery-previews-container]"
    );

    if (galleryInput && previewsContainer) {
        const template = previewsContainer.querySelector(
            "template[data-gallery-preview-template]"
        );
        const watermarkUrl =
            previewsContainer.dataset.galleryWatermarkUrl || "";
        const dropzone = document.querySelector("[data-gallery-dropzone]");
        const previewsWrapper = document.querySelector(
            "[data-gallery-previews-wrapper]"
        );
        const counterElement = document.querySelector("[data-gallery-counter]");
        const emptyState = dropzone?.querySelector(
            "[data-gallery-empty-state]"
        );
        const addMoreButton = dropzone?.querySelector(
            "[data-gallery-add-more]"
        );
        const MAX_FILES = 10;
        const canManageFiles =
            typeof window !== "undefined" &&
            typeof window.DataTransfer !== "undefined";

        let selectedFiles = [];
        let dragSourceIndex = null;
        let dragSourcePreview = null;
        let dragPlaceholder = null;

        const isFileDragEvent = (event) => {
            if (!event || !event.dataTransfer) {
                return false;
            }

            const { files, types } = event.dataTransfer;

            if (files && files.length > 0) {
                return true;
            }

            if (!types) {
                return false;
            }

            return Array.from(types).includes("Files");
        };

        const updateFileCount = () => {
            if (!counterElement) {
                return;
            }

            counterElement.textContent = `${selectedFiles.length} de ${MAX_FILES} imágenes seleccionadas`;
            counterElement.classList.toggle(
                "text-red-300",
                selectedFiles.length >= MAX_FILES
            );
            counterElement.classList.toggle(
                "text-gray-400",
                selectedFiles.length < MAX_FILES
            );
        };

        const createPreviewElement = () => {
            if (!(template instanceof HTMLTemplateElement)) {
                throw new Error("Template de galería no disponible");
            }

            const fragment = template.content.cloneNode(true);
            const element = fragment.firstElementChild;

            if (!element) {
                throw new Error(
                    "No se pudo crear el contenedor de la vista previa"
                );
            }

            return element;
        };

        const getPlaceholderElement = () => {
            if (!dragPlaceholder) {
                dragPlaceholder = document.createElement("div");
                dragPlaceholder.dataset.galleryPlaceholder = "";
                dragPlaceholder.className =
                    "flex min-h-[8rem] items-center justify-center rounded-xl border-2 border-dashed border-indigo-400/70 bg-indigo-500/10 text-xs font-medium text-indigo-200 transition-all duration-200";
                dragPlaceholder.textContent = "Suelta aquí";
            }

            return dragPlaceholder;
        };

        const ensurePlaceholderHeight = () => {
            if (!dragSourcePreview) {
                return;
            }

            const placeholder = getPlaceholderElement();
            const height = dragSourcePreview.offsetHeight;
            placeholder.style.height = height ? `${height}px` : "";
        };

        const removePlaceholder = () => {
            if (dragPlaceholder && dragPlaceholder.parentNode) {
                dragPlaceholder.parentNode.removeChild(dragPlaceholder);
            }
        };

        const animatePreviewReorder = (element) => {
            if (!(element instanceof Element)) {
                return;
            }

            element.animate(
                [
                    { transform: "scale(1)" },
                    { transform: "scale(0.97)" },
                    { transform: "scale(1)" },
                ],
                {
                    duration: 180,
                    easing: "ease-out",
                }
            );
        };

        const updatePlaceholderPosition = (preview, event) => {
            if (!previewsContainer || dragSourceIndex === null) {
                return;
            }

            const placeholder = getPlaceholderElement();
            ensurePlaceholderHeight();

            let targetPreview = preview;
            let deltaX = 0;
            let deltaY = 0;

            if (!targetPreview || targetPreview === placeholder) {
                const previews = Array.from(
                    previewsContainer.querySelectorAll("[data-gallery-preview]")
                ).filter((element) => element !== placeholder);

                if (previews.length === 0) {
                    if (placeholder.parentNode !== previewsContainer) {
                        previewsContainer.appendChild(placeholder);
                    }
                    return;
                }

                let closestPreview = null;
                let minDistance = Infinity;

                previews.forEach((element) => {
                    const rect = element.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    const offsetX = event.clientX - centerX;
                    const offsetY = event.clientY - centerY;
                    const distance = Math.hypot(offsetX, offsetY);

                    if (distance < minDistance) {
                        minDistance = distance;
                        closestPreview = element;
                        deltaX = offsetX;
                        deltaY = offsetY;
                    }
                });

                if (!closestPreview) {
                    if (placeholder.parentNode !== previewsContainer) {
                        previewsContainer.appendChild(placeholder);
                    }
                    return;
                }

                targetPreview = closestPreview;
            } else {
                const rect = targetPreview.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                deltaX = event.clientX - centerX;
                deltaY = event.clientY - centerY;
            }

            const isHorizontalDominant = Math.abs(deltaX) > Math.abs(deltaY);
            const shouldPlaceAfter = isHorizontalDominant
                ? deltaX > 0
                : deltaY > 0;

            if (shouldPlaceAfter) {
                if (targetPreview.nextSibling !== placeholder) {
                    targetPreview.after(placeholder);
                    animatePreviewReorder(targetPreview);
                }
                return;
            }

            if (targetPreview.previousSibling !== placeholder) {
                previewsContainer.insertBefore(placeholder, targetPreview);
                animatePreviewReorder(targetPreview);
            }
        };

        const getPlaceholderIndex = () => {
            if (!dragPlaceholder || !previewsContainer.contains(dragPlaceholder)) {
                return -1;
            }

            const items = Array.from(
                previewsContainer.querySelectorAll(
                    "[data-gallery-preview], [data-gallery-placeholder]"
                )
            );

            return items.indexOf(dragPlaceholder);
        };

        const updateContainerVisibility = () => {
            const hasFiles = selectedFiles.length > 0;
            const canAddMore = selectedFiles.length < MAX_FILES;

            previewsContainer.classList.toggle("hidden", !hasFiles);
            previewsWrapper?.classList.toggle("hidden", !hasFiles);
            emptyState?.classList.toggle("hidden", hasFiles);
            addMoreButton?.classList.toggle("hidden", !canAddMore);
            if (addMoreButton) {
                addMoreButton.setAttribute(
                    "aria-disabled",
                    String(!canAddMore)
                );
                addMoreButton.disabled = !canAddMore;
                const addMoreLabel = addMoreButton.querySelector(
                    "[data-gallery-add-more-label]"
                );
                if (addMoreLabel && canAddMore) {
                    addMoreLabel.textContent = hasFiles
                        ? "Agregar más fotos"
                        : "Seleccionar imágenes";
                }
            }
            galleryInput.disabled = !canAddMore;
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
            removePlaceholder();

            previewsContainer
                .querySelectorAll("[data-gallery-preview]")
                .forEach((element) => {
                    element.remove();
                });

            selectedFiles.forEach((file, index) => {
                const element = createPreviewElement();
                element.dataset.galleryPreview = "";
                element.dataset.galleryIndex = String(index);
                element.draggable = true;

                const loadingIndicator = element.querySelector(
                    "[data-gallery-loading]"
                );
                const imgBase = element.querySelector(
                    "[data-gallery-preview-image]"
                );
                const imgWater = element.querySelector(
                    "[data-gallery-preview-watermark]"
                );
                const errorEl = element.querySelector("[data-gallery-error]");
                const coverBadge = element.querySelector(
                    "[data-gallery-cover-badge]"
                );
                const filenameEl = element.querySelector(
                    "[data-gallery-filename]"
                );

                if (imgBase) {
                    imgBase.draggable = false;
                }

                if (imgWater) {
                    imgWater.draggable = false;
                }

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

            const validFiles = files.filter((file) =>
                file.type.startsWith("image/")
            );

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
            const atMaxFiles = () => selectedFiles.length >= MAX_FILES;
            dropzone.addEventListener("drop", (event) => {
                event.preventDefault();
            });

            dropzone.addEventListener("click", (event) => {
                if (
                    event.target instanceof Element &&
                    event.target.closest("[data-gallery-preview]")
                ) {
                    return;
                }

                if (atMaxFiles()) {
                    return;
                }

                galleryInput.click();
            });

            dropzone.addEventListener("keydown", (event) => {
                if (event.target !== dropzone) {
                    return;
                }

                if (event.key !== "Enter" && event.key !== " ") {
                    return;
                }

                event.preventDefault();

                if (atMaxFiles()) {
                    return;
                }

                galleryInput.click();
            });
        }

        if (addMoreButton) {
            addMoreButton.addEventListener("click", (event) => {
                event.preventDefault();
                event.stopPropagation();

                if (selectedFiles.length >= MAX_FILES) {
                    return;
                }

                galleryInput.click();
            });
        }

        galleryInput.addEventListener("change", () => {
            const files = Array.from(galleryInput.files || []);
            addFilesToSelection(files);

            // ⚠️ Quitar esto que vacía el input
            // if (canManageFiles) {
            //     galleryInput.value = "";
            // }

            updateFileInput(); // 👈 asegura que los selectedFiles estén en el input
        });

        previewsContainer.addEventListener("click", (event) => {
            const removeButton =
                event.target instanceof Element
                    ? event.target.closest("[data-gallery-remove]")
                    : null;

            if (!removeButton) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

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

            preview.classList.add("opacity-50");
            ensurePlaceholderHeight();

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
            removePlaceholder();
        });

        previewsContainer.addEventListener("dragcancel", () => {
            removePlaceholder();
        });

        previewsContainer.addEventListener("dragenter", (event) => {
            if (dragSourceIndex !== null) {
                const target = event.target;

                if (
                    target instanceof Element &&
                    target.dataset.galleryPlaceholder !== undefined
                ) {
                    return;
                }

                const preview = findPreviewElement(target);

                updatePlaceholderPosition(preview, event);
                return;
            }

            if (isFileDragEvent(event)) {
                return;
            }
        });

        previewsContainer.addEventListener("dragover", (event) => {
            if (dragSourceIndex !== null) {
                event.preventDefault();

                if (event.dataTransfer) {
                    event.dataTransfer.dropEffect = "move";
                }

                const target = event.target;

                if (
                    target instanceof Element &&
                    target.dataset.galleryPlaceholder !== undefined
                ) {
                    return;
                }

                const preview = findPreviewElement(target);

                updatePlaceholderPosition(preview, event);
                return;
            }

            if (isFileDragEvent(event)) {
                event.preventDefault();
                return;
            }
        });

        previewsContainer.addEventListener("drop", (event) => {
            if (dragSourceIndex !== null) {
                event.preventDefault();

                const placeholderIndex = getPlaceholderIndex();
                removePlaceholder();

                const [movedFile] = selectedFiles.splice(dragSourceIndex, 1);

                if (!movedFile) {
                    dragSourceIndex = null;
                    dragSourcePreview = null;
                    return;
                }

                let destinationIndex = placeholderIndex;

                if (destinationIndex < 0) {
                    destinationIndex = selectedFiles.length;
                }

                if (dragSourceIndex < destinationIndex) {
                    destinationIndex -= 1;
                }

                if (destinationIndex > selectedFiles.length) {
                    destinationIndex = selectedFiles.length;
                }

                selectedFiles.splice(destinationIndex, 0, movedFile);

                if (dragSourcePreview) {
                    dragSourcePreview.classList.remove("opacity-50");
                }

                dragSourceIndex = null;
                dragSourcePreview = null;

                renderPreviews();
                return;
            }

            if (isFileDragEvent(event)) {
                event.preventDefault();

                const files = Array.from(event.dataTransfer?.files || []);

                addFilesToSelection(files);
                removePlaceholder();
                return;
            }
        });

        previewsContainer.addEventListener("dragleave", (event) => {
            if (!isFileDragEvent(event)) {
                if (dragSourceIndex === null) {
                    return;
                }

                const related = event.relatedTarget;

                if (
                    related instanceof Element &&
                    previewsContainer.contains(related)
                ) {
                    return;
                }

                removePlaceholder();
                return;
            }
        });

        updateContainerVisibility();
    }
});
