// docs/.vitepress/config.mts

import { defineConfig } from 'vitepress'
import {
    generateChanges,
    generateVersionRewrites,
    generateVersionSidebars,
    generateVersionSwitcher
} from './data/versions'

export default defineConfig({
    title: 'sholokhov.exchange',
    description: 'Документация по модулю sholokhov.exchange',
    base: '/sholokhov.exchange.doc/',
    cleanUrls: true,
    rewrites: generateVersionRewrites(),
    head: [
        [
            'link',
            {rel: 'icon', href: '/sholokhov.exchange.doc/favicon.ico'}
        ]
    ],

    themeConfig: {
        themeConfig: {
            versionSwitcher: true,
        },
        outline: {
            level: [2,3,4],
            label: 'На этой странице'
        },
        socialLinks: [
            { icon: 'github', link: 'https://github.com/sholokhov-daniil/sholokhov.exchange' }
        ],
        nav: [
            generateChanges(),
            {text: 'Сообщить об ошибке', link: "https://github.com/sholokhov-daniil/sholokhov.exchange/issues"},
            generateVersionSwitcher(),
        ],
        sidebar: generateVersionSidebars(),
    }
});