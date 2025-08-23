import { useQuery } from "@tanstack/react-query";
import { api } from "../lib/api";
import type { SearchResponse } from "../types";

export type SearchParams = {
    q: string;
    province: string;          // np. "07"
    priority: "stable" | "urgent";
    kids?: boolean;
    days?: 30 | 60 | 90;
    sort?: "fastest";
    maxDays?: number;          // opcjonalne, raczej nie używamy gdy days
};

export function useSearch(params: SearchParams | null) {
    return useQuery({
        queryKey: ["search", params],
        queryFn: async () => {
            const res = await api.get<SearchResponse>("/api/search", { params });
            return res.data;
        },
        enabled: !!params && !!params.q && !!params.province && !!params.priority,
    });
}
