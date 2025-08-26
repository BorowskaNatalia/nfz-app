import { useEffect, useRef, useState } from "react";
import type { SearchParams } from "../hooks/useSearch";

type Props = {
    onSubmit: (p: SearchParams) => void;
};

const provinces = [
    { code: "02", name: "Dolnośląskie" },
    { code: "04", name: "Kujawsko-Pomorskie" },
    { code: "06", name: "Lubelskie" },
    { code: "07", name: "Lubuskie" },
    { code: "08", name: "Łódzkie" },
    { code: "10", name: "Małopolskie" },
    { code: "12", name: "Mazowieckie" },
    { code: "14", name: "Opolskie" },
    { code: "16", name: "Podkarpackie" },
    { code: "18", name: "Podlaskie" },
    { code: "20", name: "Pomorskie" },
    { code: "22", name: "Śląskie" },
    { code: "24", name: "Świętokrzyskie" },
    { code: "26", name: "Warmińsko-Mazurskie" },
    { code: "28", name: "Wielkopolskie" },
    { code: "30", name: "Zachodniopomorskie" },
];

type BenefitSuggestion = { name: string };

// —————————————————————————————————————————————————————
// Hook: pobieranie sugestii z debounce + AbortController
function useBenefitSuggest(query: string, limit = 8) {
    const [items, setItems] = useState<BenefitSuggestion[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const q = query.trim();
        if (q.length < 2) {
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
                const url = `/api/benefits?q=${encodeURIComponent(q)}&limit=${limit}`;
                const res = await fetch(url, { signal: ctrl.signal });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const json = await res.json();
                const data = Array.isArray(json?.data) ? json.data : [];
                const mapped: BenefitSuggestion[] = data
                    .map((x: any) => ({ name: String(x.name ?? x.attributes?.benefit ?? "") }))
                    .filter((x: BenefitSuggestion) => x.name);
                if (alive) setItems(mapped);
            } catch (e: any) {
                if (e?.name === "AbortError") return;
                if (alive) setError(e?.message ?? "Błąd pobierania");
            } finally {
                if (alive) setLoading(false);
            }
        }, 300); // debounce 300 ms

        return () => {
            alive = false;
            ctrl.abort();
            clearTimeout(t);
        };
    }, [query, limit]);

    return { items, loading, error };
}

// —————————————————————————————————————————————————————
// Komponent: pole z autouzupełnianiem (tylko wybór ze słownika)
function BenefitAutocomplete({
    value,
    onChange,
    onValidChange,
    label = "Jaki problem/świadczenie?",
}: {
    value: string;
    onChange: (v: string) => void;
    onValidChange: (ok: boolean) => void; // true tylko jeśli wybrano z listy
    label?: string;
}) {
    const { items, loading, error } = useBenefitSuggest(value, 8);
    const [open, setOpen] = useState(false);
    const [highlight, setHighlight] = useState(-1);
    const wrapRef = useRef<HTMLDivElement>(null);

    // jeżeli bieżąca wartość dokładnie pasuje do którejś z pozycji - jest „valid”
    useEffect(() => {
        const ok = items.some((i) => i.name === value);
        onValidChange(ok);
    }, [items, value, onValidChange]);

    // zamykanie listy po kliknięciu poza
    useEffect(() => {
        function onDocClick(e: MouseEvent) {
            if (!wrapRef.current) return;
            if (!wrapRef.current.contains(e.target as Node)) setOpen(false);
        }
        document.addEventListener("mousedown", onDocClick);
        return () => document.removeEventListener("mousedown", onDocClick);
    }, []);

    const select = (name: string) => {
        onChange(name);
        onValidChange(true);
        setOpen(false);
    };

    return (
        <div className="md:col-span-2" ref={wrapRef}>
            <label className="block text-sm font-medium mb-1">{label}</label>
            <div className="relative">
                <input
                    role="combobox"
                    aria-expanded={open}
                    aria-autocomplete="list"
                    className="w-full border rounded-lg px-3 py-2"
                    value={value}
                    onChange={(e) => {
                        onChange(e.target.value);
                        setOpen(true);
                        setHighlight(-1);
                        // dopóki nie kliknięto propozycji - wartość traktujemy jako niezatwierdzoną
                        onValidChange(false);
                    }}
                    onFocus={() => setOpen(true)}
                    onKeyDown={(e) => {
                        if (!open && (e.key === "ArrowDown" || e.key === "ArrowUp")) setOpen(true);
                        if (e.key === "ArrowDown") {
                            e.preventDefault();
                            setHighlight((h) => Math.min(h + 1, items.length - 1));
                        }
                        if (e.key === "ArrowUp") {
                            e.preventDefault();
                            setHighlight((h) => Math.max(h - 1, 0));
                        }
                        if (e.key === "Enter" && open) {
                            e.preventDefault();
                            if (items.length && highlight >= 0) select(items[highlight].name);
                        }
                        if (e.key === "Escape") setOpen(false);
                    }}
                    placeholder="np. kardiolog"
                />
                {open && (value.trim().length >= 2) && (
                    <div className="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow max-h-64 overflow-auto">
                        {loading && (
                            <div className="px-3 py-2 text-sm text-gray-500">Szukam…</div>
                        )}
                        {error && (
                            <div className="px-3 py-2 text-sm text-red-600">Błąd: {error}</div>
                        )}
                        {!loading && !error && items.length === 0 && (
                            <div className="px-3 py-2 text-sm text-gray-500">Brak podpowiedzi</div>
                        )}
                        {!loading && !error && items.map((it, i) => (
                            <button
                                type="button"
                                key={`${it.name}-${i}`}
                                className={`w-full text-left px-3 py-2 text-sm hover:bg-gray-100 ${i === highlight ? "bg-gray-100" : ""
                                    }`}
                                onMouseEnter={() => setHighlight(i)}
                                onMouseDown={(e) => {
                                    // onMouseDown zamiast onClick - żeby nie stracić focusa przed selekcją
                                    e.preventDefault();
                                    select(it.name);
                                }}
                            >
                                {it.name}
                            </button>
                        ))}
                    </div>
                )}
            </div>
            <p className="mt-1 text-xs text-gray-500">
                Wpisz i wybierz z listy -tylko nazwy dostępne w słowniku NFZ.
            </p>
        </div>
    );
}

// —————————————————————————————————————————————————————
// formularz z integracją BenefitAutocomplete
export function SearchForm({ onSubmit }: Props) {
    const [q, setQ] = useState("Kardiolog"); // domyślnie poprawna nazwa ze słownika
    const [qValid, setQValid] = useState<boolean>(false);
    const [province, setProvince] = useState("12"); // Mazowieckie
    const [priority, setPriority] = useState<"stable" | "urgent">("stable");
    const [kids, setKids] = useState(false);
    const [days, setDays] = useState<30 | 60 | 90 | undefined>(undefined);
    const [error, setError] = useState<string | null>(null);

    return (
        <form
            className="grid gap-3 md:grid-cols-6 bg-white p-4 rounded-2xl shadow"
            onSubmit={(e) => {
                e.preventDefault();
                if (!qValid) {
                    setError("Wybierz świadczenie z podpowiedzi (ze słownika NFZ).");
                    return;
                }
                setError(null);
                onSubmit({ q, province, priority, kids, days, sort: "fastest" });
            }}
        >
            <BenefitAutocomplete
                value={q}
                onChange={setQ}
                onValidChange={setQValid}
            />

            <div>
                <label className="block text-sm font-medium mb-1">Województwo</label>
                <select
                    className="w-full border rounded-lg px-3 py-2"
                    value={province}
                    onChange={(e) => setProvince(e.target.value)}
                >
                    {provinces.map((p) => (
                        <option key={p.code} value={p.code}>
                            {p.name}
                        </option>
                    ))}
                </select>
            </div>

            <div>
                <label className="block text-sm font-medium mb-1">Priorytet</label>
                <select
                    className="w-full border rounded-lg px-3 py-2"
                    value={priority}
                    onChange={(e) =>
                        setPriority(e.target.value as "stable" | "urgent")
                    }
                >
                    <option value="stable">stabilny</option>
                    <option value="urgent">pilny</option>
                </select>
            </div>

            <div className="flex items-center gap-2">
                <input
                    id="kids"
                    type="checkbox"
                    checked={kids}
                    onChange={(e) => setKids(e.target.checked)}
                />
                <label htmlFor="kids" className="text-sm">
                    dla dzieci
                </label>
            </div>

            <div>
                <label className="block text-sm font-medium mb-1">Filtr dni</label>
                <select
                    className="w-full border rounded-lg px-3 py-2"
                    value={days ?? ""}
                    onChange={(e) =>
                        setDays(e.target.value ? (Number(e.target.value) as 30 | 60 | 90) : undefined)
                    }
                >
                    <option value="">- brak -</option>
                    <option value="30">≤ 30 dni</option>
                    <option value="60">≤ 60 dni</option>
                    <option value="90">≤ 90 dni</option>
                </select>
            </div>

            <div className="md:col-span-6 flex items-center justify-between">
                {error ? (
                    <span className="text-sm text-red-600">{error}</span>
                ) : <span />}
                <button
                    type="submit"
                    className="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                    disabled={!qValid}
                    title={!qValid ? "Wybierz świadczenie z listy" : undefined}
                >
                    Szukaj
                </button>
            </div>
        </form>
    );
}
