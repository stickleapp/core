import { defineConfig } from "vitepress";

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "Stickle",
    description: "A customer analytics and engagement package for Laravel.",
    themeConfig: {
        // https://vitepress.dev/reference/default-theme-config
        nav: [
            { text: "Home", link: "/" },
            { text: "Guide", link: "/guide/index.html" },
            { text: "How-To", link: "/how-to/index.html" },
            { text: "StickleUI", link: "https://stickleui.app" },
        ],

        sidebar: [
            {
                text: "Introduction",
                collapsed: false,
                items: [
                    {
                        text: "What is Stickle?",
                        link: "/guide/what-is-stickle",
                    },
                    { text: "Use Cases", link: "/guide/use-cases" },
                    { text: "Installation", link: "/guide/installation" },
                    { text: "Configuration", link: "/guide/configuration" },
                    { text: "Getting Started", link: "/guide/getting-started" },
                ],
            },
            {
                text: "Ingesting Customer Data",
                collapsed: false,
                items: [
                    { text: "Javascript SDK", link: "/guide/javascript-sdk" },
                    {
                        text: "Request Middleware",
                        link: "/guide/request-middleware",
                    },
                    {
                        text: "Illuminate\\Auth Events",
                        link: "/guide/illuminate-auth-events",
                    },
                ],
            },
            {
                text: "Customer Segments",
                collapsed: false,
                items: [
                    {
                        text: "What are Segments?",
                        link: "/guide/what-are-segments",
                    },
                    {
                        text: "Creating Segments",
                        link: "/guide/creating-segments",
                    },
                    {
                        text: "Eloquent Methods",
                        link: "/guide/eloquent-methods",
                    },
                ],
            },
            {
                text: "Tracking Historical Data",
                collapsed: false,
                items: [
                    {
                        text: "Model Attributes",
                        link: "/guide/tracking-model-attributes",
                    },
                    {
                        text: "Segment Statistics",
                        link: "/guide/tracking-segments",
                    },
                ],
            },
            {
                text: "Aggregating Data",
                collapsed: false,
                items: [
                    {
                        text: "User Attributes",
                        link: "/guide/aggregate-user-attributes",
                    },
                    {
                        text: "Group Attributes",
                        link: "/guide/aggregate-group-attributes",
                    },
                ],
            },
            {
                text: "Event Listeners",
                collapsed: false,
                items: [
                    {
                        text: "User Events",
                        link: "/guide/listeners-user-events",
                    },
                    { text: "Page Views", link: "/guide/listeners-page-views" },
                    {
                        text: "Segment Events",
                        link: "/guide/listeners-segment-events",
                    },
                    {
                        text: "Model Attribute Changes",
                        link: "/guide/listeners-model-attribute-changes",
                    },
                    {
                        text: "Illuminate\\Auth Events",
                        link: "/guide/listeners-illuminate-auth-events",
                    },
                ],
            },
            {
                text: "Querying Customer Data",
                collapsed: false,
                items: [
                    { text: "Custom Scopes", link: "/guide/scopes" },
                    { text: "API Endpoints", link: "/guide/endpoints" },
                ],
            },
            {
                text: "StickleUI",
                collapsed: false,
                items: [
                    {
                        text: "Getting Started",
                        link: "/guide/ui-getting-started",
                    },
                    { text: "Customizing", link: "/guide/ui-customizing" },
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
