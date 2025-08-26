import { useEffect, useRef, useState } from "react";
import { useBenefitSuggest } from "../hooks/useBenefitSuggest";

type Props = {
    value: string;
    onChange: (v: string) => void;
    onValidChange: (ok: boolean) => void; // true dopiero po wyborze z listy
    label?: string;
};

export default function BenefitAutocomplete({ value, onChange, onValidChange, label = "Jaki problem/świadczenie?" }: Props) {
    const { data: items = [], isLoading: loading, error } = useBenefitSuggest(value, 8);;
    const [open, setOpen] = useState(false);
    const [highlight, setHighlight] = useState(-1);
    const wrapRef = useRef<HTMLDivElement>(null);

    // „valid” tylko gdy aktualny value dokładnie pasuje do pozycji z listy
    useEffect(() => {
        onValidChange(items.some(i => i.name === value));
    }, [items, value, onValidChange]);

    // zamknij listę po kliknięciu poza
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
                    onChange={(e) => { onChange(e.target.value); onValidChange(false); setOpen(true); setHighlight(-1); }}
                    onFocus={() => setOpen(true)}
                    onKeyDown={(e) => {
                        if (!open && (e.key === "ArrowDown" || e.key === "ArrowUp")) setOpen(true);
                        if (e.key === "ArrowDown") { e.preventDefault(); setHighlight(h => Math.min(h + 1, items.length - 1)); }
                        if (e.key === "ArrowUp") { e.preventDefault(); setHighlight(h => Math.max(h - 1, 0)); }
                        if (e.key === "Enter" && open && highlight >= 0) { e.preventDefault(); select(items[highlight].name); }
                        if (e.key === "Escape") setOpen(false);
                    }}
                    placeholder="np. poradnia kardiologiczna"
                />
                {open && value.trim().length >= 2 && (
                    <div className="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow max-h-64 overflow-auto">
                        {loading && <div className="px-3 py-2 text-sm text-gray-500">Szukam…</div>}
                        {error && <div className="px-3 py-2 text-sm text-red-600">Błąd: {typeof error === "string" ? error : error.message}</div>}
                        {!loading && !error && items.length === 0 && (
                            <div className="px-3 py-2 text-sm text-gray-500">Brak podpowiedzi</div>
                        )}
                        {!loading && !error && items.map((it, i) => (
                            <button
                                type="button"
                                key={`${it.name}-${i}`}
                                className={`w-full text-left px-3 py-2 text-sm hover:bg-gray-100 ${i === highlight ? "bg-gray-100" : ""}`}
                                onMouseEnter={() => setHighlight(i)}
                                onMouseDown={(e) => { e.preventDefault(); select(it.name); }} // mousedown, by nie stracić focusa
                            >
                                {it.name}
                            </button>
                        ))}
                    </div>
                )}
            </div>
            <p className="mt-1 text-xs text-gray-500">Wpisz i wybierz z listy - tylko nazwy ze słownika NFZ.</p>
        </div>
    );
}
