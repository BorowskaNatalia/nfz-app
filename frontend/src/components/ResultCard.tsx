import type { SearchItem } from "../types";

export function ResultCard({ item }: { item: SearchItem }) {
    const p = item.provider;
    const a = item.appointment;

    return (
        <div className="bg-white rounded-2xl shadow p-4 flex flex-col gap-2">
            <div className="flex justify-between items-baseline">
                <h3 className="text-lg font-semibold">{p.name}</h3>
                <div className="text-sm">
                    pierwszy termin: <span className="font-medium">{a.firstAvailableDate}</span>
                </div>
            </div>
            <div className="text-sm text-gray-700">
                {p.address}
            </div>
            <div className="text-sm flex gap-4">
                <span>kolejka: <b>{a.queueSize}</b></span>
                <span>priorytet: <b>{a.priority}</b></span>
                <span>dla dzieci: <b>{p.forChildren ? "tak" : "nie"}</b></span>
            </div>
            <div className="text-sm">
                {p.phone && <a href={`tel:${p.phone}`} className="text-blue-600 hover:underline">Zadzwoń: {p.phone}</a>}
            </div>
        </div>
    );
}
