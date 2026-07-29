import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import fs from "fs";
import path from "path";

// This is an installable package, not an application. Two different outputs:
//
//   `vite build` -> ./public/build, committed to the repo and copied into an
//                   installing app by `vendor:publish --tag=package-assets`.
//                   This is the artifact that ships, so it must never depend on
//                   testbench being present.
//
//   `vite serve` -> testbench's public dir, so the HMR `hot` file lands where
//                   the running workbench app looks for it. Local dev only.
//
// The `hot` file sits next to the published assets so a single code path -- the
// scoped Vite instance in CoreServiceProvider -- resolves both HMR and built
// assets. Keep this path in sync with `useHotFile()` there.
const TESTBENCH_PUBLIC = "./vendor/orchestra/testbench-core/laravel/public";
const PUBLISH_PATH = "vendor/stickleapp/core";

export default defineConfig(({ command }) => {
  const useTestbench = command === "serve" && fs.existsSync(TESTBENCH_PUBLIC);
  const publicDirectory = useTestbench ? TESTBENCH_PUBLIC : "public";

  return {
    plugins: [
      tailwindcss(),
      laravel({
        input: ["resources/css/app.css", "resources/js/app.js"],
        publicDirectory,
        buildDirectory: "build",
        hotFile: path.join(publicDirectory, PUBLISH_PATH, "hot"),
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
  };
});
