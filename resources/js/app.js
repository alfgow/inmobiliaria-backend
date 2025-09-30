import axios from "axios";
import Chart from "chart.js/auto";
import Choices from "choices.js";
import "choices.js/styles.css";
import L from "leaflet";
import "leaflet-control-geocoder";
import "leaflet-control-geocoder/dist/Control.Geocoder.css";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import "leaflet/dist/leaflet.css";

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

    const destacadoCheckbox = document.getElementById("destacado");

    if (destacadoCheckbox instanceof HTMLInputElement) {
        const hiddenInput = destacadoCheckbox.form?.querySelector(
            'input[type="hidden"][name="destacado"]'
        );
        const syncHiddenInput = (checked) => {
            if (hiddenInput) {
                hiddenInput.value = checked ? "1" : "0";
            }
        };
        const getCsrfToken = () => {
            return (
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || ""
            );
        };

        let previousState = destacadoCheckbox.checked;

        syncHiddenInput(previousState);

        destacadoCheckbox.addEventListener("change", async () => {
            const updateUrl = destacadoCheckbox.dataset.updateUrl || "";
            const nextState = destacadoCheckbox.checked;

            syncHiddenInput(nextState);

            if (!updateUrl) {
                previousState = nextState;

                return;
            }

            const headers = { Accept: "application/json" };
            const csrfToken = getCsrfToken();

            if (csrfToken) {
                headers["X-CSRF-TOKEN"] = csrfToken;
            }

            try {
                destacadoCheckbox.dataset.updating = "true";

                await axios.patch(
                    updateUrl,
                    { destacado: nextState },
                    { headers }
                );

                previousState = nextState;
            } catch (error) {
                console.error(
                    "No fue posible actualizar el estado de destacado.",
                    error
                );

                window.alert(
                    "No fue posible actualizar el estado destacado del inmueble. Intenta nuevamente."
                );

                destacadoCheckbox.checked = previousState;
                syncHiddenInput(previousState);
            } finally {
                delete destacadoCheckbox.dataset.updating;
            }
        });
    }

    const initializePropertiesMap = () => {
        const container = document.getElementById("properties-map");

        if (!container || container.__propertiesMapInitialized) {
            return;
        }

        container.__propertiesMapInitialized = true;

        const parseCoordinate = (value) => {
            const numeric = Number.parseFloat(value);

            return Number.isFinite(numeric) ? numeric : null;
        };

        const escapeHtml = (value) => {
            return String(value ?? "").replace(/[&<>"']/g, (character) => {
                switch (character) {
                    case "&":
                        return "&amp;";
                    case "<":
                        return "&lt;";
                    case ">":
                        return "&gt;";
                    case '"':
                        return "&quot;";
                    case "'":
                        return "&#039;";
                    default:
                        return character;
                }
            });
        };

        let properties = [];

        try {
            const raw = container.dataset.properties;

            if (raw) {
                const parsed = JSON.parse(raw);

                if (Array.isArray(parsed)) {
                    properties = parsed
                        .map((property) => {
                            const latitude = parseCoordinate(
                                property?.latitude
                            );
                            const longitude = parseCoordinate(
                                property?.longitude
                            );

                            if (latitude === null || longitude === null) {
                                return null;
                            }

                            return {
                                ...property,
                                latitude,
                                longitude,
                            };
                        })
                        .filter(Boolean);
                }
            }
        } catch (error) {
            console.error(
                "No fue posible parsear la información de inmuebles para el mapa.",
                error
            );
        }

        const defaultCenter = [19.432608, -99.133209];
        const hasProperties = properties.length > 0;
        const startingPoint = hasProperties
            ? [properties[0].latitude, properties[0].longitude]
            : defaultCenter;

        const map = L.map(container, {
            attributionControl: true,
            zoomControl: true,
        });

        map.setView(startingPoint, hasProperties ? 13 : 5);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
            maxZoom: 19,
        }).addTo(map);

        const bounds = L.latLngBounds();
        const availableMarkerIcon = L.divIcon({
            className: "property-marker property-marker--available",
            iconSize: [30, 42],
            iconAnchor: [15, 42],
            popupAnchor: [0, -32],
        });

        properties.forEach((property) => {
            const position = [property.latitude, property.longitude];
            const marker = L.marker(
                position,
                property?.is_available ? { icon: availableMarkerIcon } : {}
            ).addTo(map);

            bounds.extend(position);

            const imageUrl =
                typeof property.image_url === "string"
                    ? property.image_url
                    : "";
            const manageUrl =
                typeof property.manage_url === "string"
                    ? property.manage_url
                    : "";
            const title = escapeHtml(property.title ?? "Inmueble");
            const address = escapeHtml(property.address ?? "");
            const price = escapeHtml(property.price ?? "");
            const statusNameRaw =
                property?.status && typeof property.status.name === "string"
                    ? property.status.name
                    : "";
            const statusColorRaw =
                property?.status && typeof property.status.color === "string"
                    ? property.status.color
                    : "";
            const statusName = escapeHtml(statusNameRaw);
            const statusColor = statusColorRaw.trim();
            const statusBadgeStyle = statusColor
                ? ` style="background-color: ${escapeHtml(statusColor)}"`
                : "";
            const statusBadge = statusName
                ? `<span class="property-status-badge"${statusBadgeStyle}>${statusName}</span>`
                : "";

            const imageContent = imageUrl
                ? `<img src="${imageUrl}" alt="${title}" class="mb-3 h-32 w-full rounded-lg object-cover" />`
                : "";

            const manageButton = manageUrl
                ? `<a href="${manageUrl}" class="manage-property-button mt-3 w-full inline-flex items-center justify-center rounded-xl
         bg-indigo-600/90 px-4 py-2.5 text-sm font-medium text-white
         shadow-[0_8px_20px_rgba(79,70,229,0.35)]
         hover:bg-indigo-500/90 hover:shadow-[0_6px_16px_rgba(79,70,229,0.45)]
         focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/70
         transition-all duration-300 ease-out">Gestionar inmueble</a>`
                : "";

            const popupContent = `
                <div class="space-y-3 text-left">
                    ${imageContent}
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-base font-semibold text-gray-900">${title}</h3>
                            ${statusBadge}
                        </div>
                        <p class="text-sm text-gray-600">${address}</p>
                        <p class="text-sm font-semibold text-indigo-600">${price}</p>
                    </div>
                    ${manageButton}
                </div>
            `;

            marker.bindPopup(popupContent);
        });

        if (hasProperties && bounds.isValid()) {
            map.fitBounds(bounds, { padding: [40, 40] });
        }
    };

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
            return Number.isFinite(coordinate) ? coordinate.toFixed(6) : "";
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
                estado: pickFirst(
                    address.state,
                    address["state_name"],
                    address.region
                ),
            };
        };

        const selectIds = ["codigo_postal", "colonia", "municipio", "estado"];

        const setSelectValue = (select, value) => {
            if (!select) {
                return;
            }

            const normalized = typeof value === "string" ? value.trim() : "";
            const previousValue = select.value;
            let optionsSnapshot = null;

            if (normalized) {
                let option = Array.from(select.options).find((item) => {
                    return item.value === normalized;
                });

                if (!option) {
                    option = new Option(normalized, normalized, true, true);
                    select.add(option);
                    optionsSnapshot = Array.from(select.options).map(
                        (item) => ({
                            value: item.value,
                            label: item.label,
                            selected: item.selected,
                        })
                    );
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
                    if (optionsSnapshot) {
                        choicesInstance.clearChoices();
                        choicesInstance.setChoices(
                            optionsSnapshot,
                            "value",
                            "label",
                            true
                        );
                    } else {
                        choicesInstance.removeActiveItems();
                    }

                    if (normalized) {
                        choicesInstance.setChoiceByValue(normalized);
                    } else {
                        const placeholder =
                            select.querySelector("option[value='']");

                        if (placeholder) {
                            choicesInstance.setChoiceByValue(placeholder.value);
                        } else {
                            choicesInstance.removeActiveItems();
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

        let activeGeocodeToken = 0;

        const geocoders = [
            geocoderFactory({
                geocodingQueryParams: {
                    countrycodes: "mx",
                    viewbox: "-99.3645,19.049,-98.94,19.592",
                    bounded: 1,
                },
            }),
            geocoderFactory({
                geocodingQueryParams: {
                    countrycodes: "mx",
                    viewbox: "-100.18,18.343,-98.61,20.364",
                    bounded: 1,
                },
            }),
            geocoderFactory({
                geocodingQueryParams: {
                    countrycodes: "mx",
                },
            }),
            geocoderFactory(),
        ].filter(Boolean);

        const attemptScopedGeocode = (query, geocoderIndex, requestToken) => {
            if (requestToken !== activeGeocodeToken) {
                return;
            }

            if (geocoderIndex >= geocoders.length) {
                return;
            }

            const currentGeocoder = geocoders[geocoderIndex];

            if (
                !currentGeocoder ||
                typeof currentGeocoder.geocode !== "function"
            ) {
                attemptScopedGeocode(query, geocoderIndex + 1, requestToken);
                return;
            }

            currentGeocoder.geocode(query, (results) => {
                if (requestToken !== activeGeocodeToken) {
                    return;
                }

                if (Array.isArray(results) && results.length > 0) {
                    handleGeocodeResults(results);
                    return;
                }

                attemptScopedGeocode(query, geocoderIndex + 1, requestToken);
            });
        };

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

            attemptScopedGeocode(trimmed, 0, requestToken);
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

    const updateSearchableSelectPreview = (select) => {
        if (!select) {
            return;
        }

        const container = select.closest("[data-searchable-select]");

        if (!container) {
            return;
        }

        const previewCard = container.querySelector("[data-property-preview]");

        if (!previewCard) {
            return;
        }

        const imageElement = previewCard.querySelector(
            "[data-property-preview-image]"
        );
        const titleElement = previewCard.querySelector(
            "[data-property-preview-title]"
        );
        const addressElement = previewCard.querySelector(
            "[data-property-preview-address]"
        );
        const operationElement = previewCard.querySelector(
            "[data-property-preview-operation]"
        );
        const typeElement = previewCard.querySelector(
            "[data-property-preview-type]"
        );
        const priceElement = previewCard.querySelector(
            "[data-property-preview-price]"
        );
        const habitacionesElement = previewCard.querySelector(
            "[data-property-preview-habitaciones]"
        );
        const banosElement = previewCard.querySelector(
            "[data-property-preview-banos]"
        );
        const estacionamientosElement = previewCard.querySelector(
            "[data-property-preview-estacionamientos]"
        );
        const metrosElement = previewCard.querySelector(
            "[data-property-preview-metros]"
        );

        const selectedOption = select.value
            ? select.selectedOptions?.[0] || null
            : null;

        const resetPreview = () => {
            if (titleElement) {
                titleElement.textContent = "";
            }

            if (addressElement) {
                addressElement.textContent = "";
            }

            if (operationElement) {
                operationElement.textContent = "";
                operationElement.classList.add("hidden");
            }

            if (typeElement) {
                typeElement.textContent = "";
                typeElement.classList.add("hidden");
            }

            if (priceElement) {
                priceElement.textContent = "";
                priceElement.classList.add("hidden");
            }

            if (habitacionesElement) {
                habitacionesElement.textContent = "";
            }

            if (banosElement) {
                banosElement.textContent = "";
            }

            if (estacionamientosElement) {
                estacionamientosElement.textContent = "";
            }

            if (metrosElement) {
                metrosElement.textContent = "";
            }

            if (imageElement) {
                const placeholder = imageElement.dataset.placeholder || "";

                if (placeholder) {
                    imageElement.src = placeholder;
                } else {
                    imageElement.removeAttribute("src");
                }
            }

            previewCard.classList.add("hidden");
            previewCard.classList.remove("flex");
        };

        if (!selectedOption || !selectedOption.value) {
            resetPreview();

            return;
        }

        const {
            coverImage: coverImageUrl = "",
            title = "",
            fullAddress = "",
            operation = "",
            type = "",
            price = "",
            habitaciones = "",
            banos = "",
            estacionamientos = "",
            metrosCuadrados = "",
        } = selectedOption.dataset;

        if (titleElement) {
            titleElement.textContent = title || "";
        }

        if (addressElement) {
            addressElement.textContent = fullAddress || "";
        }

        if (operationElement) {
            operationElement.textContent = operation || "";
            operationElement.classList.toggle("hidden", !operation);
        }

        if (typeElement) {
            typeElement.textContent = type || "";
            typeElement.classList.toggle("hidden", !type);
        }

        if (priceElement) {
            let formattedPrice = "";

            if (price) {
                const numericPrice = Number(price);

                if (Number.isFinite(numericPrice)) {
                    formattedPrice = new Intl.NumberFormat("es-MX", {
                        style: "currency",
                        currency: "MXN",
                        maximumFractionDigits: 0,
                    }).format(numericPrice);
                } else {
                    formattedPrice = price;
                }
            }

            priceElement.textContent = formattedPrice;
            priceElement.classList.toggle("hidden", !formattedPrice);
        }

        if (habitacionesElement) {
            habitacionesElement.textContent = habitaciones || "-";
        }

        if (banosElement) {
            banosElement.textContent = banos || "-";
        }

        if (estacionamientosElement) {
            estacionamientosElement.textContent = estacionamientos || "-";
        }

        if (metrosElement) {
            const formattedMetros = metrosCuadrados
                ? `${metrosCuadrados} m²`
                : "-";

            metrosElement.textContent = formattedMetros;
        }

        if (imageElement) {
            const placeholder = imageElement.dataset.placeholder || "";

            if (coverImageUrl) {
                imageElement.src = coverImageUrl;
            } else if (placeholder) {
                imageElement.src = placeholder;
            } else {
                imageElement.removeAttribute("src");
            }
        }

        previewCard.classList.remove("hidden");
        previewCard.classList.add("flex");
    };

    document
        .querySelectorAll("[data-searchable-select] select")
        .forEach((select) => {
            initializeChoicesSelect(select);

            if (select.__searchableSelectPreviewInitialized) {
                updateSearchableSelectPreview(select);

                return;
            }

            const handleChange = () => updateSearchableSelectPreview(select);

            select.addEventListener("change", handleChange);
            updateSearchableSelectPreview(select);

            select.__searchableSelectPreviewInitialized = true;
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
        const fieldOrder = ["codigo_postal", "colonia", "municipio", "estado"];
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

            if (
                triggered &&
                previousValue &&
                normalizedValues.includes(previousValue)
            ) {
                newValue = previousValue;
            }

            const fragment = document.createDocumentFragment();
            const placeholderOption = document.createElement("option");

            placeholderOption.value = "";
            placeholderOption.textContent =
                placeholder || "Selecciona una opción";

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
            const initialSearchTerm = fields[firstAvailableField]?.choices
                ?.searchInput
                ? fields[firstAvailableField].choices.searchInput.value || ""
                : "";

            requestOptionsUpdate(firstAvailableField, initialSearchTerm);
        }
    };

    document.querySelectorAll("[data-postal-selector]").forEach((container) => {
        initializePostalSelector(container);
    });

    initializePropertiesMap();
    initializeInmuebleMap();

    document.querySelectorAll("form[data-swal-loader]").forEach((form) => {
        form.addEventListener("submit", () => {
            if (!window.Swal || form.dataset.swalLoaderActive === "true") {
                return;
            }

            form.dataset.swalLoaderActive = "true";
            form.dataset.submitting = "true";

            const title =
                form.dataset.swalLoaderTitle || "Registrando contacto";
            const text =
                form.dataset.swalLoaderText ||
                "Estamos guardando la información...";

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

    const statusSelect = document.getElementById("estatus_id");

    if (statusSelect) {
        const operationSelect = document.getElementById("operacion");
        const commissionPercentageInput = document.getElementById(
            "commission_percentage"
        );
        const commissionAmountInput =
            document.getElementById("commission_amount");
        const commissionStatusIdInput = document.getElementById(
            "commission_status_id"
        );
        const commissionStatusNameInput = document.getElementById(
            "commission_status_name"
        );
        const priceInput = document.getElementById("precio");
        const closingKeywords = ["vendido", "rentado", "arrendado", "cerrado"];
        const getStatusChoicesInstance = () => {
            return (
                choicesInstances.get(statusSelect) ||
                statusSelect.__choicesInstance ||
                null
            );
        };
        let isRestoringStatus = false;
        let previousStatusValue = statusSelect.value || "";
        const getOptionKeywords = (option) => {
            if (!option) {
                return [];
            }

            const possibleValues = [
                option.dataset.statusSlug,
                option.dataset.statusName,
                option.textContent,
            ];

            return possibleValues
                .map((value) =>
                    String(value || "")
                        .toLowerCase()
                        .trim()
                )
                .filter((value) => value !== "");
        };

        const parseNumericValue = (value) => {
            if (typeof value === "number") {
                return Number.isFinite(value) ? value : null;
            }

            if (typeof value !== "string") {
                return null;
            }

            const normalized = value.replace(/,/g, ".").trim();

            if (normalized === "") {
                return null;
            }

            const numeric = Number.parseFloat(normalized);

            return Number.isFinite(numeric) ? numeric : null;
        };

        const formatCurrency = (amount) => {
            const numericAmount = parseNumericValue(amount) ?? 0;

            return new Intl.NumberFormat("es-MX", {
                style: "currency",
                currency: "MXN",
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(numericAmount);
        };

        const computeCommissionAmount = (percentage) => {
            const numericPercentage = parseNumericValue(percentage);
            const price = parseNumericValue(priceInput?.value);

            if (numericPercentage === null || price === null) {
                return null;
            }

            return (price * numericPercentage) / 100;
        };

        const getOptionLabel = (option) => {
            if (!option) {
                return "";
            }

            const datasetName = option.dataset.statusName;

            if (typeof datasetName === "string" && datasetName.trim() !== "") {
                return datasetName.trim();
            }

            return (option.textContent || "").trim();
        };

        const isClosingStatus = (option) => {
            if (!option) {
                return false;
            }

            const label = getOptionLabel(option).toLowerCase();

            return closingKeywords.some((keyword) => label.includes(keyword));
        };

        const updateHiddenCommissionFields = (
            percentageValue,
            amountValue,
            statusIdValue,
            statusLabelValue
        ) => {
            if (commissionPercentageInput) {
                const numericPercentage = parseNumericValue(percentageValue);
                commissionPercentageInput.value =
                    numericPercentage === null ? "" : String(numericPercentage);
            }

            if (commissionAmountInput) {
                const numericAmount = parseNumericValue(amountValue);
                commissionAmountInput.value =
                    numericAmount === null
                        ? ""
                        : String(numericAmount.toFixed(2));
            }

            if (commissionStatusIdInput) {
                commissionStatusIdInput.value = statusIdValue || "";
            }

            if (commissionStatusNameInput) {
                commissionStatusNameInput.value = statusLabelValue || "";
            }
        };

        const setStatusValueSilently = (value) => {
            const normalizedValue = value || "";
            isRestoringStatus = true;

            const statusChoicesInstance = getStatusChoicesInstance();

            if (statusChoicesInstance) {
                try {
                    if (normalizedValue === "") {
                        statusChoicesInstance.removeActiveItems();
                        const placeholderOption =
                            statusSelect.querySelector("option[value='']");

                        if (placeholderOption) {
                            statusChoicesInstance.setChoiceByValue(
                                placeholderOption.value
                            );
                        }
                    } else {
                        statusChoicesInstance.setChoiceByValue(normalizedValue);
                    }
                } catch (error) {
                    console.warn(
                        "No fue posible sincronizar el estatus con Choices.js",
                        error
                    );
                }
            }

            statusSelect.value = normalizedValue;

            window.setTimeout(() => {
                isRestoringStatus = false;
            }, 0);
        };

        const notifyStatusFiltered = (statusLabel, operationValue) => {
            const readableOperation = (
                operationSelect?.selectedOptions?.[0]?.textContent ||
                operationSelect?.value ||
                operationValue ||
                ""
            )
                .toString()
                .trim();
            const label = statusLabel ? `"${statusLabel}" ` : "";
            const operationLabel = readableOperation
                ? `la operación ${readableOperation}`
                : "la operación seleccionada";
            const message = `${
                statusLabel
                    ? `El estatus ${label}no es compatible con ${operationLabel}.`
                    : `El estatus seleccionado no es compatible con ${operationLabel}.`
            } Selecciona un nuevo estatus disponible.`;

            if (window.Swal) {
                window.Swal.fire({
                    icon: "info",
                    title: "Actualiza el estatus",
                    text: message,
                    confirmButtonText: "Entendido",
                });
                return;
            }

            window.alert(message);
        };

        const filterStatusOptionsByOperation = (trigger = "init") => {
            const normalizedOperation = (operationSelect?.value || "")
                .toString()
                .trim()
                .toLowerCase();
            const selectedOption = statusSelect.selectedOptions[0] || null;
            const selectedValue = statusSelect.value || "";
            let removedStatusLabel = "";
            let shouldResetStatus = false;

            Array.from(statusSelect.options).forEach((option) => {
                if (!option || option.value === "") {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const optionKeywords = getOptionKeywords(option);
                let shouldHide = false;

                if (normalizedOperation === "renta") {
                    shouldHide = optionKeywords.some((keyword) =>
                        keyword.includes("vendido")
                    );
                } else if (normalizedOperation === "venta") {
                    shouldHide = optionKeywords.some((keyword) =>
                        keyword.includes("rentado")
                    );
                }

                option.hidden = shouldHide;
                option.disabled = shouldHide;

                if (shouldHide && option.value === selectedValue) {
                    removedStatusLabel = getOptionLabel(option);
                    shouldResetStatus = true;
                }
            });

            if (shouldResetStatus) {
                setStatusValueSilently("");
                previousStatusValue = "";
                updateHiddenCommissionFields("", "", "", "");

                if (trigger === "change") {
                    notifyStatusFiltered(
                        removedStatusLabel,
                        normalizedOperation
                    );
                }
            }

            previousStatusValue = statusSelect.value || "";
        };

        const showCommissionModal = async (option) => {
            const label = getOptionLabel(option);
            const initialPercentage =
                parseNumericValue(commissionPercentageInput?.value) ?? 0;

            if (!window.Swal) {
                const fallback = window.prompt(
                    `Ingresa el porcentaje de comisión para "${label}"`,
                    String(initialPercentage || "")
                );

                if (fallback === null) {
                    return { confirmed: false };
                }

                const percentage = parseNumericValue(fallback);

                if (percentage === null || percentage < 0) {
                    window.alert(
                        "Debes ingresar un porcentaje de comisión válido."
                    );

                    return { confirmed: false };
                }

                const amount = computeCommissionAmount(percentage) ?? 0;

                updateHiddenCommissionFields(
                    percentage,
                    amount,
                    option.value,
                    label
                );

                return { confirmed: true };
            }

            let handlePriceChange;

            const result = await window.Swal.fire({
                title: "Registrar comisión",
                html: `
                    <div class="space-y-4 text-left">
                        <div class="rounded-2xl bg-gray-900 p-5 text-gray-100 shadow-xl">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">
                                        Monto de operación
                                    </p>
                                    <p id="swal-operation-amount" class="mt-1 text-lg font-semibold text-white">
                                        ${formatCurrency(priceInput?.value)}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-gray-800 pt-4">
                                    <label class="text-sm font-medium text-gray-300" for="swal-commission-percentage">
                                        Porcentaje de comisión
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            id="swal-commission-percentage"
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            maxlength="3"
                                            class="w-16 rounded-md border border-gray-700 bg-gray-800 px-3 py-2 text-center text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            value="${initialPercentage}"
                                        >
                                        <span class="text-sm font-semibold text-gray-400">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 rounded-lg border border-gray-800 bg-gray-950/70 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">
                                    Ganancia estimada
                                </p>
                                <p id="swal-commission-amount" class="mt-2 text-lg font-semibold text-white">
                                    ${formatCurrency(
                                        computeCommissionAmount(
                                            initialPercentage
                                        ) ?? 0
                                    )}
                                </p>
                            </div>
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: "Guardar",
                cancelButtonText: "Cancelar",
                customClass: {
                    popup: "swal-dark-popup",
                    title: "swal-dark-title",
                    htmlContainer: "swal-dark-content",
                    confirmButton:
                        "swal2-confirm rounded-2xl bg-indigo-500 px-6 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/60",
                    cancelButton:
                        "swal2-cancel rounded-2xl border border-white/10 bg-slate-800/70 px-6 py-2 text-sm font-semibold text-slate-200 transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500/60",
                },
                buttonsStyling: false,
                preConfirm: () => {
                    const input = document.getElementById(
                        "swal-commission-percentage"
                    );
                    const amountPreview = document.getElementById(
                        "swal-commission-amount"
                    );
                    const operationAmount = document.getElementById(
                        "swal-operation-amount"
                    );

                    if (!input || !amountPreview) {
                        return false;
                    }

                    const percentage = parseNumericValue(input.value);

                    if (
                        percentage === null ||
                        percentage < 0 ||
                        percentage > 100
                    ) {
                        window.Swal.showValidationMessage(
                            "Ingresa un porcentaje entre 0 y 100"
                        );

                        return false;
                    }

                    const amount = computeCommissionAmount(percentage) ?? 0;

                    amountPreview.textContent = formatCurrency(amount);

                    if (operationAmount) {
                        operationAmount.textContent = formatCurrency(
                            priceInput?.value
                        );
                    }

                    return {
                        percentage,
                        amount,
                    };
                },
                didOpen: () => {
                    const input = document.getElementById(
                        "swal-commission-percentage"
                    );
                    const amountPreview = document.getElementById(
                        "swal-commission-amount"
                    );
                    const operationAmount = document.getElementById(
                        "swal-operation-amount"
                    );

                    if (!input || !amountPreview) {
                        return;
                    }

                    const updateOperationAmount = () => {
                        if (operationAmount) {
                            operationAmount.textContent = formatCurrency(
                                priceInput?.value
                            );
                        }
                    };

                    const refreshPreview = () => {
                        const amount =
                            computeCommissionAmount(input.value) ?? 0;
                        amountPreview.textContent = formatCurrency(amount);
                    };

                    handlePriceChange = () => {
                        updateOperationAmount();
                        refreshPreview();
                    };

                    input.addEventListener("input", refreshPreview);
                    updateOperationAmount();
                    refreshPreview();

                    if (priceInput) {
                        priceInput.addEventListener("input", handlePriceChange);
                    }
                },
                willClose: () => {
                    if (priceInput && handlePriceChange) {
                        priceInput.removeEventListener(
                            "input",
                            handlePriceChange
                        );
                    }
                },
            });

            if (!result.isConfirmed || !result.value) {
                return { confirmed: false };
            }

            const { percentage, amount } = result.value;

            updateHiddenCommissionFields(
                percentage,
                amount,
                option.value,
                label
            );

            return { confirmed: true };
        };

        const getInmuebleUpdateForm = () => {
            return document.getElementById("inmueble-update-form");
        };

        const submitInmuebleUpdateForm = () => {
            const form = getInmuebleUpdateForm();

            if (!form) {
                return;
            }

            form.dataset.submitting = "true";

            if (typeof form.requestSubmit === "function") {
                form.requestSubmit();
            } else {
                form.submit();
            }
        };

        const handleStatusChange = async () => {
            const selectedOption = statusSelect.selectedOptions[0] || null;
            const selectedValue = statusSelect.value || "";

            const form = getInmuebleUpdateForm();

            if (form?.dataset.submitting === "true") {
                previousStatusValue = selectedValue;
                return;
            }

            if (isRestoringStatus) {
                previousStatusValue = selectedValue;
                return;
            }

            if (!selectedOption || selectedValue === "") {
                updateHiddenCommissionFields("", "", "", "");
                previousStatusValue = selectedValue;
                return;
            }

            if (!isClosingStatus(selectedOption)) {
                updateHiddenCommissionFields("", "", "", "");
                submitInmuebleUpdateForm();
                previousStatusValue = selectedValue;
                return;
            }

            const result = await showCommissionModal(selectedOption);

            if (result.confirmed) {
                submitInmuebleUpdateForm();
                previousStatusValue = selectedValue;
                return;
            }

            setStatusValueSilently(previousStatusValue);

            const previousOption = Array.from(statusSelect.options).find(
                (option) => option.value === previousStatusValue
            );

            if (!previousOption || !isClosingStatus(previousOption)) {
                updateHiddenCommissionFields("", "", "", "");
            }
        };

        filterStatusOptionsByOperation("init");

        const selectedOption = statusSelect.selectedOptions[0] || null;

        if (selectedOption && isClosingStatus(selectedOption)) {
            updateHiddenCommissionFields(
                commissionPercentageInput?.value || "",
                commissionAmountInput?.value || "",
                selectedOption.value,
                getOptionLabel(selectedOption)
            );
        }

        statusSelect.addEventListener("change", handleStatusChange);

        if (operationSelect) {
            operationSelect.addEventListener("change", () => {
                filterStatusOptionsByOperation("change");
            });
        }

        if (priceInput) {
            priceInput.addEventListener("input", () => {
                if (
                    !commissionPercentageInput ||
                    !commissionAmountInput ||
                    !commissionStatusIdInput ||
                    commissionStatusIdInput.value === ""
                ) {
                    return;
                }

                const percentage = parseNumericValue(
                    commissionPercentageInput.value
                );

                if (percentage === null) {
                    return;
                }

                const amount = computeCommissionAmount(percentage);

                if (amount === null) {
                    commissionAmountInput.value = "";
                    return;
                }

                commissionAmountInput.value = String(amount.toFixed(2));
            });
        }
    }

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
            if (
                !dragPlaceholder ||
                !previewsContainer.contains(dragPlaceholder)
            ) {
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
