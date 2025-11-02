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
      {{ getMessage('SHOLOKHOV_EXCHANGE_DETAIL_ENTITY_UI_SOURCE_CSV_SEPARATOR') }}
    </template>
    <template #content>
      <Input v-model="model.separator" maxlength="1" />
    </template>
  </GridRow>

  <GridRow>
    <template #title>
      {{ getMessage('SHOLOKHOV_EXCHANGE_DETAIL_ENTITY_UI_SOURCE_CSV_ENCLOSURE') }}
    </template>
    <template #content>
      <Input v-model="model.enclosure" />
    </template>
  </GridRow>

  <GridRow>
    <template #title>
      {{ getMessage('SHOLOKHOV_EXCHANGE_DETAIL_ENTITY_UI_SOURCE_CSV_ESCAPE') }}
    </template>
    <template #content>
      <Input v-model="model.escape" />
    </template>
  </GridRow>
</template>

<script setup>
import {defineModel} from 'vue';
import {GridRow, Input} from "ui";
import {getMessage} from "utils";

const model = defineModel();
</script>