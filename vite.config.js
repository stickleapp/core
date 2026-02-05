import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import fs from "fs";

// Detect if running in package development (testbench) or installed in a Laravel app
const testbenchPublic = "./vendor/orchestra/testbench-core/laravel/public";
const isPackageDev = fs.existsSync(testbenchPublic);
const publicDirectory = isPackageDev ? testbenchPublic : "public";

export default defineConfig({
  plugins: [
    tailwindcss(),
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      publicDirectory,
      buildDirectory: "build",
      refresh: true,
    }),
  ],
  server: {
    // Make Vite accessible from other devices on your network
    host: "0.0.0.0",
    // Set the correct port
    port: 5174,
    // Ensure HMR works correctly
    hmr: {
      host: "localhost",
    },
  },
});
