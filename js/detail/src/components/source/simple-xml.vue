<template>
  <GridRow>
    <template #title>
      {{ getMessage('SHOLOKHOV_EXCHANGE_DETAIL_ENTITY_UI_FILE_PATH') }}
    </template>
    <template #content>
      <Input v-model="model.path" />
    </template>
  </GridRow>

  <GridRow>
    <template #title>
      {{ getMessage('SHOLOKHOV_EXCHANGE_DETAIL_ENTITY_UI_SOURCE_XML_ROOT') }}
    </template>
    <template #content>
      <Input v-model="model.rootTag" />
    </template>
  </GridRow>
</template>

<script setup>
import {defineModel} from 'vue';
import {GridRow, Input} from "ui";
import {getMessage} from "utils";

const model = defineModel();
</script>