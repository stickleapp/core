import { defineConfig } from "vitepress";

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "Stickle",
    description: "A customer analytics and engagement package for Laravel.",
    // ... other config
    rewrites: {
        "core/guide/index.md": "core/guide/what-is-stickle.md",
        "core/guide/": "core/guide/what-is-stickle.md",
    },
    base: "/core/",
    themeConfig: {
        // https://vitepress.dev/reference/default-theme-config
        nav: [
            { text: "Home", link: "/" },
            { text: "Guide", link: "/guide/" },
        ],
        sidebar: [
            {
                text: "Introduction",
                collapsed: false,
                items: [
                    {
                        text: "What is Stickle?",
                        link: "/guide/index",
                    },
                ],
            },
            {
                text: "Getting Started",
                collapsed: false,
                items: [
                    { text: "Installation", link: "/guide/installation" },
                    { text: "Basic Setup", link: "/guide/basic-setup" },
                    { text: "Configuration", link: "/guide/configuration" },
                ],
            },
            {
                text: "Core Features",
                collapsed: false,
                items: [
                    {
                        text: "Tracking Attributes",
                        link: "/guide/tracking-attributes",
                    },
                    { text: "Customer Segments", link: "/guide/segments" },
                    { text: "Filters", link: "/guide/filters" },
                    { text: "Event Listeners", link: "/guide/event-listeners" },
                    {
                        text: "JavaScript Tracking",
                        link: "/guide/javascript-tracking",
                    },
                    { text: "StickleUI Dashboard", link: "/guide/stickle-ui" },
                ],
            },
            {
                text: "Reference",
                collapsed: false,
                items: [
                    { text: "API Endpoints", link: "/guide/api-endpoints" },
                    { text: "Filter Reference", link: "/guide/filter-reference" },
                    { text: "Events Reference", link: "/guide/events-reference" },
                ],
            },
            {
                text: "Advanced",
                collapsed: false,
                items: [
                    { text: "Recipes", link: "/guide/recipes" },
                    { text: "Deployment", link: "/guide/deployment" },
                    { text: "Troubleshooting", link: "/guide/troubleshooting" },
                ],
            },
        ],

        socialLinks: [
            {
                icon: "github",
                link: "https://github.com/stickleapp/laravel-stickle",
            },
        ],
    },
});
