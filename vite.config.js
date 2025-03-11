import { defineConfig } from "vite";
import { resolve } from "path";

export default defineConfig({
    build: {
        // Output directory for the bundled files
        outDir: "build",

        // Ensure we generate clean builds
        emptyOutDir: true,

        // Configure the library mode for package bundling
        lib: {
            // Entry point is your app.js file
            entry: resolve(__dirname, "resources/js/main.js"),

            // Output name for your bundle
            name: "app",

            // Specify the formats you want to support
            formats: ["es", "umd"],

            // Output filename pattern
            fileName: (format) => `app.${format}.js`,
        },

        // Rollup specific options
        rollupOptions: {
            // No external dependencies - Laravel Echo will be bundled
            output: {
                // Configure output options as needed
                manualChunks: undefined,
            },
        },
    },

    // Resolve configuration for importing files
    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/js"),
        },
    },
});
