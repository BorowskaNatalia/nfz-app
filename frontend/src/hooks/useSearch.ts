import { useQuery } from "@tanstack/react-query";
import { api } from "../lib/api";
import type { SearchResponse } from "../types";

export type SearchParams = {
    q: string;
    province: string;
    priority: "stable" | "urgent";
    kids?: boolean;
    days?: 30 | 60 | 90;
    sort?: "fastest";
    maxDays?: number;
    city?: string;
};

function sanitize(obj: Record<string, unknown>) {
    // wywalamy undefined, null, pusty string
    return Object.fromEntries(
        Object.entries(obj).filter(([_, v]) => v !== undefined && v !== null && v !== "")
    );
}

export function useSearch(params: SearchParams | null) {
    return useQuery({
        queryKey: ["search", params],
        queryFn: async () => {
            const res = await api.get<SearchResponse>("/search", {
                params: params ? sanitize(params as Record<string, unknown>) : undefined,
            });
            return res.data;
        },
        enabled: !!params && !!params.q && !!params.province && !!params.priority,
    });
}
