
import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";

const { resolve } = require("path");

// https://vitejs.dev/config/
export default defineConfig(({ command, mode }) => ({
    build: {
        emptyOutDir: true,
        minify: mode !== 'development',
        sourcemap: mode === 'development',
        cssCodeSplit: false,
        rollupOptions: {
            external: ["vue", /^@vue-mf\/.+/],
            input: resolve(__dirname, "src/main.js"),
            output: {
                dir: "../../../storage/public/vue-mf/toolbar-dialogs",
                manualChunks: undefined,
                format: "es",
                assetFileNames: "assets/vue-mf-toolbar-dialogs.min.[ext]",
                entryFileNames: "vue-mf-toolbar-dialogs.min.js",
            },
            preserveEntrySignatures: true,
        },
    },
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: resolve(__dirname, "/storage/public/vue-mf/toolbar-dialogs/assets"),
                },
            },
        }),
    ],
}));
