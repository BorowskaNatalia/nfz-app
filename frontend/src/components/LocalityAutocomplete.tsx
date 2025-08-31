import { useEffect, useRef, useState } from "react";
import { useLocalitySuggest } from "../hooks/useLocalitySuggest";

type Props = {
    province: string;
    value: string;
    onChange: (v: string) => void;
    onValidChange: (ok: boolean) => void; // true dopiero po wyborze z listy
    label?: string;
    disabled?: boolean;
};

export default function LocalityAutocomplete({
    province, value, onChange, onValidChange, label = "Miasto", disabled
}: Props) {
    const { items, loading, error } = useLocalitySuggest(value, province, 10);
    const [open, setOpen] = useState(false);
    const [highlight, setHighlight] = useState(-1);
    const wrapRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        onValidChange(items.some(i => i.name === value));
    }, [items, value, onValidChange]);

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

    const isDisabled = disabled || !province;

    return (
        <div className="md:col-span-2" ref={wrapRef}>
            <label className="block text-sm font-medium mb-1">{label} (opcjonalnie)</label>
            <div className="relative">
                <input
                    role="combobox"
                    aria-expanded={open}
                    aria-autocomplete="list"
                    className="w-full border rounded-lg px-3 py-2 disabled:bg-gray-100"
                    value={value}
                    disabled={isDisabled}
                    onChange={(e) => {
                        onChange(e.target.value);
                        setOpen(true);
                        setHighlight(-1);
                        onValidChange(false);
                    }}
                    onFocus={() => !isDisabled && setOpen(true)}
                    onKeyDown={(e) => {
                        if (isDisabled) return;
                        if (!open && (e.key === "ArrowDown" || e.key === "ArrowUp")) setOpen(true);
                        if (e.key === "ArrowDown") { e.preventDefault(); setHighlight(h => Math.min(h + 1, items.length - 1)); }
                        if (e.key === "ArrowUp") { e.preventDefault(); setHighlight(h => Math.max(h - 1, 0)); }
                        if (e.key === "Enter" && open && highlight >= 0) { e.preventDefault(); select(items[highlight].name); }
                        if (e.key === "Escape") setOpen(false);
                    }}
                    placeholder={isDisabled ? "Wybierz najpierw województwo" : "np. Warszawa"}
                />

                {open && !isDisabled && value.trim().length >= 2 && (
                    <div className="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow max-h-64 overflow-auto">
                        {loading && <div className="px-3 py-2 text-sm text-gray-500">Szukam…</div>}
                        {error && <div className="px-3 py-2 text-sm text-red-600">Błąd: {error}</div>}
                        {!loading && !error && items.length === 0 && (
                            <div className="px-3 py-2 text-sm text-gray-500">Brak podpowiedzi</div>
                        )}
                        {!loading && !error && items.map((it, i) => (
                            <button
                                type="button"
                                key={`${it.name}-${i}`}
                                className={`w-full text-left px-3 py-2 text-sm hover:bg-gray-100 ${i === highlight ? "bg-gray-100" : ""}`}
                                onMouseEnter={() => setHighlight(i)}
                                onMouseDown={(e) => { e.preventDefault(); select(it.name); }}
                            >
                                {it.name}
                            </button>
                        ))}
                    </div>
                )}
            </div>
            <p className="mt-1 text-xs text-gray-500">
                Słownik NFZ. Najpierw wybierz województwo, potem wpisz min. 3 znaki.
            </p>
        </div>
    );
}
