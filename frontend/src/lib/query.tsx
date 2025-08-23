import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import type { ReactNode } from 'react';
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";

const client = new QueryClient();

export function QueryProvider({ children }: { children: ReactNode }) {
    return (
        <QueryClientProvider client={client}>
            {children}
            <ReactQueryDevtools initialIsOpen={false} />
        </QueryClientProvider>
    );
}
