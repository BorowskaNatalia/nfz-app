import { useQuery } from "@tanstack/react-query";

export type BenefitSuggestion = { name: string };

export function useBenefitSuggest(query: string, limit = 8) {
    const q = query.trim();

    return useQuery<BenefitSuggestion[], Error>({
        queryKey: ["benefit-suggest", q, limit],
        enabled: q.length >= 2,
        staleTime: 60_000,
        queryFn: async () => {
            const res = await fetch(`/api/benefits?q=${encodeURIComponent(q)}&limit=${limit}`, {
                headers: { Accept: "application/json" },
                cache: "no-store",
            });

            if (!res.ok) {
                const reqId = res.headers.get("X-Request-Id") ?? "";
                const body = await res.text().catch(() => "");
                throw new Error(`HTTP ${res.status}${reqId ? ` [${reqId}]` : ""}${body ? `: ${body}` : ""}`);
            }

            const json = await res.json().catch(() => ({ data: [] as any[] }));
            const raw = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);

            return (raw as any[])
                .map((x) => ({ name: String(x?.name ?? x?.attributes?.benefit ?? "").trim() }))
                .filter((x) => x.name.length > 0);
        },
    });
}
