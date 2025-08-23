export type SearchItem = {
    provider: {
        id: string;
        name: string;
        address: string;
        phone: string | null;
        website: string | null;
        forChildren: boolean;
        location: { lat: number | null; lng: number | null };
    };
    appointment: {
        firstAvailableDate: string; // "YYYY-MM-DD"
        queueSize: number;
        priority: "STABLE" | "URGENT";
        lastUpdated: string; // ISO
    };
    distanceKm: number;
};

export type SearchResponse = {
    data: SearchItem[];
    meta: {
        count: number;
        lastUpdated: string | null;
        filters?: {
            requestedMaxDays: number;
            appliedMaxDays: number | null;
            relaxation: string[];
        };
    };
};
