import axios from "axios";

const raw = import.meta.env.VITE_API_URL || "http://localhost:8000";
const base = raw.replace(/\/+$/, "");

// teraz każda ścieżka jest względem .../api
export const api = axios.create({
    baseURL: `${base}/api`,
    headers: { Accept: "application/json" },
});
