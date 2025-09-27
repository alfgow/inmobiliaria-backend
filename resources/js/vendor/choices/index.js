const defaultClassNames = {
    container: "choices",
    containerOpen: "choices--open",
    containerDisabled: "choices--disabled",
    input: "choices__search",
    select: "choices__select",
};

const toArray = (value) => {
    if (!value) {
        return [];
    }

    return Array.isArray(value) ? value : [value];
};

export default class Choices {
    constructor(element, options = {}) {
        if (!element || element.nodeName !== "SELECT") {
            throw new TypeError("Choices requiere un elemento <select> válido");
        }

        this.select = element;
        this.config = {
            searchEnabled: true,
            searchPlaceholderValue: "Buscar...",
            classNames: {},
            ...options,
        };

        this.classNames = {
            ...defaultClassNames,
            ...(this.config.classNames || {}),
        };

        this.container = this._resolveContainer();
        this.container.classList.add(this.classNames.container);
        this.select.classList.add(this.classNames.select);

        this.searchInput = this._createSearchInput();
        this.suppressChangeEvent = false;
        this.isDestroyed = false;

        this._bindEvents();
        this._observeMutations();
        this.refresh({ resetSearch: true, dispatchChange: false });
    }

    _resolveContainer() {
        const container = this.select.closest("[data-searchable-select]");

        if (container) {
            return container;
        }

        const wrapper = document.createElement("div");
        wrapper.dataset.searchableSelect = "";
        this.select.parentNode?.insertBefore(wrapper, this.select);
        wrapper.appendChild(this.select);

        return wrapper;
    }

    _createSearchInput() {
        if (!this.config.searchEnabled) {
            return null;
        }

        const input = document.createElement("input");
        input.type = "search";
        input.autocomplete = "off";
        input.placeholder = this.config.searchPlaceholderValue || "Buscar...";
        input.className = this.classNames.input;

        this.container.insertBefore(input, this.select);

        return input;
    }

    _bindEvents() {
        if (!this.searchInput) {
            return;
        }

        this._handleInput = () => {
            this.filter();
        };

        this._handleSearch = () => {
            this.filter();
        };

        this._handleKeydown = (event) => {
            if (event.key !== "Enter") {
                return;
            }

            event.preventDefault();

            const firstVisibleOption = this._getOptions().find((option) => {
                return !option.hidden && option.value !== "";
            });

            if (!firstVisibleOption) {
                return;
            }

            this.setChoiceByValue(firstVisibleOption.value);
        };

        this._handleBlur = () => {
            if (!this.searchInput) {
                return;
            }

            if (this.searchInput.value.trim() !== "") {
                return;
            }

            this._getOptions().forEach((option) => {
                option.hidden = false;
            });
        };

        this.searchInput.addEventListener("input", this._handleInput);
        this.searchInput.addEventListener("search", this._handleSearch);
        this.searchInput.addEventListener("keydown", this._handleKeydown);
        this.searchInput.addEventListener("blur", this._handleBlur);
    }

    _observeMutations() {
        this._mutationObserver = new MutationObserver(() => {
            this.refresh({ resetSearch: true, dispatchChange: false });
        });

        this._mutationObserver.observe(this.select, { childList: true });
    }

    _getOptions() {
        return Array.from(this.select.options);
    }

    filter() {
        if (!this.searchInput) {
            return;
        }

        const term = this.searchInput.value.trim().toLowerCase();
        const options = this._getOptions();

        options.forEach((option) => {
            if (option.value === "") {
                option.hidden = false;
                return;
            }

            const source = option.dataset.searchable || option.textContent || "";
            const matches = term === "" || source.toLowerCase().includes(term);

            option.hidden = !matches;
        });

        const selectedOption = this.select.selectedOptions[0];

        if (!selectedOption || !selectedOption.hidden) {
            return;
        }

        this._setValue("", { dispatchChange: true });
    }

    refresh({ resetSearch = true, dispatchChange = true } = {}) {
        if (this.isDestroyed) {
            return;
        }

        const previousValue = this.select.value;
        const previousSuppressState = this.suppressChangeEvent;

        this.suppressChangeEvent = !dispatchChange;

        if (resetSearch && this.searchInput) {
            this.searchInput.value = "";
        }

        this._getOptions().forEach((option) => {
            option.hidden = false;
        });

        if (this.searchInput) {
            this.filter();
        }

        this.suppressChangeEvent = previousSuppressState;

        if (dispatchChange && previousValue !== this.select.value) {
            this.select.dispatchEvent(new Event("change", { bubbles: true }));
        }
    }

    clearChoices() {
        this.select.innerHTML = "";
        this.refresh({ resetSearch: true, dispatchChange: false });
    }

    setChoices(items, valueKey = "value", labelKey = "label", replaceChoices = false) {
        const choices = toArray(items);

        if (replaceChoices) {
            this.clearChoices();
        }

        const fragment = document.createDocumentFragment();

        choices.forEach((item) => {
            const value = item?.[valueKey];
            const label = item?.[labelKey];

            if (value === undefined || value === null) {
                return;
            }

            const option = document.createElement("option");
            option.value = String(value);
            option.textContent = label === undefined ? String(value) : String(label);

            if (item && item.selected) {
                option.selected = true;
            }

            if (item && item.disabled) {
                option.disabled = true;
            }

            fragment.appendChild(option);
        });

        this.select.appendChild(fragment);
        this.refresh({ resetSearch: false, dispatchChange: false });
    }

    setChoiceByValue(value) {
        this._setValue(value, { dispatchChange: true });
    }

    removeActiveItems() {
        this._setValue("", { dispatchChange: true });
    }

    _setValue(value, { dispatchChange = true } = {}) {
        const stringValue = value === undefined || value === null ? "" : String(value);
        const previousValue = this.select.value;

        this.select.value = stringValue;

        if (!dispatchChange || previousValue === stringValue) {
            return;
        }

        this.select.dispatchEvent(new Event("change", { bubbles: true }));
    }

    destroy() {
        if (this.isDestroyed) {
            return;
        }

        if (this._mutationObserver) {
            this._mutationObserver.disconnect();
            this._mutationObserver = null;
        }

        if (this.searchInput) {
            this.searchInput.removeEventListener("input", this._handleInput);
            this.searchInput.removeEventListener("search", this._handleSearch);
            this.searchInput.removeEventListener("keydown", this._handleKeydown);
            this.searchInput.removeEventListener("blur", this._handleBlur);
            this.searchInput.remove();
        }

        this.select.classList.remove(this.classNames.select);
        this.container.classList.remove(this.classNames.container);

        this.isDestroyed = true;
    }
}
