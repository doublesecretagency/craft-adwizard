module.exports = {
    markdown: {
        anchor: { level: [2, 3] },
        extendMarkdown(md) {
            let markup = require('vuepress-theme-craftdocs/markup');
            md.use(markup);
        },
    },
    base: '/ad-wizard/',
    title: 'Ad Wizard plugin for Craft CMS',
    plugins: [
        [
            'vuepress-plugin-clean-urls',
            {
                normalSuffix: '/',
                indexSuffix: '/',
                notFoundPath: '/404.html',
            },
        ],
    ],
    theme: 'craftdocs',
    themeConfig: {
        codeLanguages: {
            php: 'PHP',
            twig: 'Twig',
            js: 'JavaScript',
        },
        logo: '/images/icon.svg',
        searchMaxSuggestions: 10,
        nav: [
            {text: 'Getting Started️', link: '/getting-started/'},
            {
                text: 'How It Works',
                items: [
                    {text: 'Creating your Ads', link: '/creating-your-ads/'},
                    {text: 'Embedding your Ads', link: '/embedding-your-ads/'},
                    {text: 'Get Ads with an Element Query', link: '/get-ads-with-an-element-query/'},
                    {text: 'Field Types', link: '/field-types/'},
                    {text: 'Seeing your Ad statistics', link: '/seeing-your-ad-statistics/'},
                    {text: 'Custom Fields', link: '/custom-fields/'},
                    {text: 'The `options` parameter', link: '/the-options-parameter/'},
                    {text: 'Image transforms', link: '/image-transforms/'},
                    {text: 'Valid Ads', link: '/valid-ads/'},
                    {text: 'Events', link: '/events/'},
                ]
            },
            {
                text: 'More',
                items: [
                    {text: 'Double Secret Agency', link: 'https://www.doublesecretagency.com/plugins'},
                    {text: 'Our other Craft plugins', link: 'https://plugins.doublesecretagency.com', target:'_self'},
                ]
            },
        ],
        sidebar: {
            '/': [
                'getting-started',
                'creating-your-ads',
                'embedding-your-ads',
                'get-ads-with-an-element-query',
                'field-types',
                'seeing-your-ad-statistics',
                'custom-fields',
                'the-options-parameter',
                'image-transforms',
                'valid-ads',
                'events',
            ],
        }
    }
};
