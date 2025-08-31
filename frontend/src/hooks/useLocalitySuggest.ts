import { useEffect, useState } from "react";
import { api } from "../lib/api";

export type LocalitySuggestion = { name: string };

export function useLocalitySuggest(q: string, province: string, limit = 10) {
    const [items, setItems] = useState<LocalitySuggestion[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const name = q.trim();

        if (!province || name.length < 2) {
            setItems([]);
            setLoading(false);
            setError(null);
            return;
        }

        let alive = true;
        const ctrl = new AbortController();
        const t = setTimeout(async () => {
            try {
                setLoading(true);
                setError(null);
                const res = await api.get("/localities", {
                    params: { q: name, province, limit },
                    signal: ctrl.signal as any,
                });

                const raw = Array.isArray(res.data?.data) ? res.data.data : [];

                // normalizacja: string[] -> {name}[], ale obsłuż też ewentualne obiekty
                const normalized: LocalitySuggestion[] = raw
                    .map((x: any) => ({
                        name: typeof x === "string" ? x : String(x?.name ?? x?.label ?? x?.value ?? ""),
                    }))
                    .filter((x: LocalitySuggestion) => x.name);

                if (alive) setItems(normalized);
            } catch (e: any) {
                if (e?.name === "CanceledError" || e?.code === "ERR_CANCELED") return;
                if (alive) setError(e?.message ?? "Błąd pobierania");
            } finally {
                if (alive) setLoading(false);
            }
        }, 300); // debounce - odczekaj chwilę zanim zaczniesz szukać

        return () => {
            alive = false;
            ctrl.abort();
            clearTimeout(t);
        };
    }, [q, province, limit]);

    return { items, loading, error };
}
