import { useState } from "react";
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

export function SearchForm({ onSubmit }: Props) {
    const [q, setQ] = useState("kardiolog");
    const [province, setProvince] = useState("12"); // Mazowieckie
    const [priority, setPriority] = useState<"stable" | "urgent">("stable");
    const [kids, setKids] = useState(false);
    const [days, setDays] = useState<30 | 60 | 90 | undefined>(undefined);

    return (
        <form
            className="grid gap-3 md:grid-cols-6 bg-white p-4 rounded-2xl shadow"
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit({ q, province, priority, kids, days, sort: "fastest" });
            }}
        >
            <div className="md:col-span-2">
                <label className="block text-sm font-medium mb-1">Jaki problem/świadczenie?</label>
                <input
                    className="w-full border rounded-lg px-3 py-2"
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                    placeholder="np. kardiolog"
                />
            </div>

            <div>
                <label className="block text-sm font-medium mb-1">Województwo</label>
                <select
                    className="w-full border rounded-lg px-3 py-2"
                    value={province}
                    onChange={(e) => setProvince(e.target.value)}
                >
                    {provinces.map((p) => (
                        <option key={p.code} value={p.code}>{p.name}</option>
                    ))}
                </select>
            </div>

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

            <div className="flex items-center gap-2">
                <input id="kids" type="checkbox" checked={kids} onChange={(e) => setKids(e.target.checked)} />
                <label htmlFor="kids" className="text-sm">dla dzieci</label>
            </div>

            <div>
                <label className="block text-sm font-medium mb-1">Filtr dni (miękki)</label>
                <select
                    className="w-full border rounded-lg px-3 py-2"
                    value={days ?? ""}
                    onChange={(e) => setDays(e.target.value ? (Number(e.target.value) as 30 | 60 | 90) : undefined)}
                >
                    <option value="">— brak —</option>
                    <option value="30">≤ 30 dni</option>
                    <option value="60">≤ 60 dni</option>
                    <option value="90">≤ 90 dni</option>
                </select>
            </div>

            <div className="md:col-span-6 flex justify-end">
                <button
                    type="submit"
                    className="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                >
                    Szukaj
                </button>
            </div>
        </form>
    );
}
