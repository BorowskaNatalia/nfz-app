import { useState } from "react";
import { SearchForm } from "./components/SearchForm";
import { useSearch, type SearchParams } from "./hooks/useSearch";
import { ResultCard } from "./components/ResultCard";

export default function App() {
  const [params, setParams] = useState<SearchParams | null>(null);
  const { data, isLoading, isError, error } = useSearch(params);

  return (
    <div className="max-w-5xl mx-auto p-4 space-y-4">
      <h1 className="text-2xl font-bold">NFZ Finder — MVP</h1>

      <SearchForm onSubmit={(p) => setParams(p)} />

      {isLoading && <div className="text-sm">Szukam…</div>}
      {isError && <div className="text-sm text-red-600">Błąd: {(error as Error).message}</div>}

      {data && (
        <>
          <div className="text-sm text-gray-700 flex justify-between">
            <div>Wyników: <b>{data.meta.count}</b></div>
            <div>Ostatnia aktualizacja: <b>{data.meta.lastUpdated ?? "—"}</b></div>
          </div>

          {data.meta.filters && (
            <div className="text-xs text-gray-500">
              Filtr dni: proszono o {data.meta.filters.requestedMaxDays}, zastosowano {String(data.meta.filters.appliedMaxDays ?? "all")}
              {" "}({data.meta.filters.relaxation.join(" → ")})
            </div>
          )}

          <div className="grid gap-3">
            {data.data.map((it) => (
              <ResultCard key={`${it.provider.id}-${it.appointment.firstAvailableDate}`} item={it} />
            ))}
          </div>
        </>
      )}
    </div>
  );
}
