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
});
