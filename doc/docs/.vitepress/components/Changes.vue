<script setup lang="ts">
import {computed} from "vue";
import {DefaultTheme, useRouter, useData} from "vitepress";
import VPNavBarMenuLink from "vitepress/dist/client/theme-default/components/VPNavBarMenuLink.vue";

const props = defineProps<{
  versioningPlugin: { versions: DefaultTheme.NavItem[], latestVersion: DefaultTheme.NavItem }
  screenMenu?: boolean
}>();

const router = useRouter();
const {site} = useData();

const currentLink = computed(() => {
  const base = site.value.base.endsWith('/') ? site.value.base.slice(0, -1) : site.value.base;

  for (const v of props.versioningPlugin.versions) {
    if (router.route.path.startsWith(base + v.dir)) {
      return {
        text: 'Что нового?',
        link: v.link
      };
    }
  }

  return {};
})
</script>

<template>
  <VPNavBarMenuLink :item="currentLink" />
</template>