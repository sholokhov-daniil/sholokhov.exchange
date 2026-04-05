// https://vitepress.dev/guide/custom-theme

import type { Theme } from 'vitepress'
import DefaultTheme from 'vitepress/theme'
import Layout from './Layout.vue'
import VersionSwitcher from '../components/VersionSwitcher.vue'
import Changes from '../components/Changes.vue'
import './style.css'

export default {
  ...DefaultTheme,
  Layout,
  enhanceApp({app}) {
    app.component('VersionSwitcher', VersionSwitcher);
    app.component('VersionChanges', Changes)
  }
} satisfies Theme
