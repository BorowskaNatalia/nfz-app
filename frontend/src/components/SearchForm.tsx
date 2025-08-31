import { useEffect, useRef, useState } from "react";
import type { SearchParams } from "../hooks/useSearch";
import { api } from "../lib/api";
import LocalityAutocomplete from "./LocalityAutocomplete";

type Props = {
    onSubmit: (p: SearchParams) => void;
};

const provinces = [
    { code: "01", name: "Dolnośląskie" },
    { code: "02", name: "Kujawsko-Pomorskie" },
    { code: "03", name: "Lubelskie" },
    { code: "04", name: "Lubuskie" },
    { code: "05", name: "Łódzkie" },
    { code: "06", name: "Małopolskie" },
    { code: "07", name: "Mazowieckie" },      // <= poprawny kod NFZ
    { code: "08", name: "Opolskie" },
    { code: "09", name: "Podkarpackie" },
    { code: "10", name: "Podlaskie" },
    { code: "11", name: "Pomorskie" },
    { code: "12", name: "Śląskie" },
    { code: "13", name: "Świętokrzyskie" },
    { code: "14", name: "Warmińsko-Mazurskie" },
    { code: "15", name: "Wielkopolskie" },
    { code: "16", name: "Zachodniopomorskie" },
];

type BenefitSuggestion = { name: string };

// —————————————————————————————————————————————————————
// Hook: pobieranie sugestii przez backend (axios + debounce + AbortController)
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
                const res = await api.get("/benefits", {
                    params: { q, limit },
                    signal: ctrl.signal as any,
                });
                const data = Array.isArray(res.data?.data) ? res.data.data : [];
                const mapped: BenefitSuggestion[] = data
                    .map((x: any) => ({ name: String(x.name ?? x.attributes?.benefit ?? "") }))
                    .filter((x: BenefitSuggestion) => x.name);
                if (alive) setItems(mapped);
            } catch (e: any) {
                if (e?.name === "CanceledError" || e?.code === "ERR_CANCELED") return;
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
                    placeholder="np. poradnia kardiologiczna"
                />
                {open && value.trim().length >= 2 && (
                    <div className="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow max-h-64 overflow-auto">
                        {loading && <div className="px-3 py-2 text-sm text-gray-500">Szukam…</div>}
                        {error && <div className="px-3 py-2 text-sm text-red-600">Błąd: {error}</div>}
                        {!loading && !error && items.length === 0 && (
                            <div className="px-3 py-2 text-sm text-gray-500">Brak podpowiedzi</div>
                        )}
                        {!loading && !error &&
                            items.map((it, i) => (
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
                Wpisz i wybierz z listy — tylko nazwy dostępne w słowniku NFZ.
            </p>
        </div>
    );
}

// —————————————————————————————————————————————————————
// Formularz z BenefitAutocomplete + LocalityAutocomplete
export function SearchForm({ onSubmit }: Props) {
    const [q, setQ] = useState("Kardiolog"); // domyślnie poprawna nazwa ze słownika
    const [qValid, setQValid] = useState<boolean>(false);
    const [province, setProvince] = useState("07"); // Mazowieckie (poprawny kod)
    const [priority, setPriority] = useState<"stable" | "urgent">("stable");
    const [kids, setKids] = useState(false);
    const [days, setDays] = useState<30 | 60 | 90 | undefined>(undefined);

    // Miasto przez autocomplete + walidacja „wybrane z listy”
    const [city, setCity] = useState("");
    const [cityValid, setCityValid] = useState(false);

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
                onSubmit({
                    q,
                    province,
                    priority,
                    kids: kids || undefined,
                    days,
                    sort: "fastest",
                    city: cityValid ? city : undefined, // tylko zatwierdzony wybór
                });
            }}
        >
            {/* Świadczenie */}
            <BenefitAutocomplete value={q} onChange={setQ} onValidChange={setQValid} />

            {/* Województwo */}
            <div>
                <label className="block text-sm font-medium mb-1">Województwo</label>
                <select
                    className="w-full border rounded-lg px-3 py-2"
                    value={province}
                    onChange={(e) => {
                        setProvince(e.target.value);
                        // zmiana województwa unieważnia wybrane wcześniej miasto
                        setCity("");
                        setCityValid(false);
                    }}
                >
                    {provinces.map((p) => (
                        <option key={p.code} value={p.code}>
                            {p.name}
                        </option>
                    ))}
                </select>
            </div>

            {/* Miasto (autocomplete NFZ /localities?name=&province=) */}
            <LocalityAutocomplete
                province={province}
                value={city}
                onChange={setCity}
                onValidChange={setCityValid}
                label="Miasto"
            />

            {/* Priorytet */}
            <div>
                <label className="block text-sm font-medium mb-1">Priorytet</label>
                <select
                    className="w-full border rounded-lg px-3 py-2"
                    value={priority}
                    onChange={(e) => setPriority(e.target.value as "stable" | "urgent")}
                >
                    <option value="stable">stabilny</option>
                    <option value="urgent">pilny</option>
                </select>
            </div>

            {/* Dla dzieci */}
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

            {/* Filtr dni */}
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

            {/* Submit + błędy */}
            <div className="md:col-span-6 flex items-center justify-between">
                {error ? <span className="text-sm text-red-600">{error}</span> : <span />}
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
