import "./bootstrap";
import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

// --- Auto-refresh CSRF token & session ping ---
function refreshCsrfToken() {
    window.axios
        .get("/")
        .then((response) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(response.data, "text/html");
            const token = doc.querySelector('meta[name="csrf-token"]');
            if (token) {
                window.axios.defaults.headers.common["X-CSRF-TOKEN"] =
                    token.content;
            }
        })
        .catch(() => {
            /* ignore */
        });
}

function sessionPing() {
    // Ping a lightweight endpoint to keep session alive
    window.axios.post("/api/ping").catch(() => {
        /* ignore */
    });
}

setInterval(
    () => {
        refreshCsrfToken();
        sessionPing();
    },
    5 * 60 * 1000,
); // setiap 5 menit
