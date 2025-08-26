// vite.config.ts
export default {
  server: {
    proxy: {
      "/api": {
        target: "http://localhost:8000", // Laravel
        changeOrigin: true,
        secure: false,
      },
    },
  },
};
