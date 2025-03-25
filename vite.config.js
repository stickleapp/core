import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            publicDirectory: "./vendor/orchestra/testbench-core/laravel/public", // Ensure this points to your package's public directory
            // Specify the build directory for testbench
            buildDirectory: "build",
            refresh: true,
        }),
    ],
    server: {
        // Make Vite accessible from other devices on your network
        host: "0.0.0.0",
        // Set the correct port
        port: 5173,
        // Ensure HMR works correctly
        hmr: {
            host: "localhost",
        },
    },
});
