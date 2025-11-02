<template>
  <GridRow>
    <template #title>
      {{ getMessage('SHOLOKHOV_EXCHANGE_DETAIL_UI_TITLE_RENDER_IBLOCK_SELECT_IBLOCK') }}
    </template>
    <template #content>
      <EntitySelector
          v-model="model.entityId"
          :options="iBlockOptions"
      />
    </template>
  </GridRow>
</template>

<script setup>
import {defineModel} from 'vue';
import {GridRow, EntitySelector} from "ui";
import {getMessage} from "utils";

const model = defineModel();

const iBlockOptions = {
  multiple: false,
  addButtonCaption: 'SHOLOHKOV_EXCHANGE_UI_ENTITY_SELECTOR_DIALOG_ADD_BUTTON_CAPTION_SELECT',
  dialogOptions: {
    entities: [
      {
        id: 'sholokhov-exchange-iblock',
        dynamicSearch: true,
        dynamicLoad: true
      }
    ]
  }
};
</script>