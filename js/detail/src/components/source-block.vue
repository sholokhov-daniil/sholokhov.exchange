<script setup>
import {computed, defineModel} from 'vue';
import {getMessage} from "utils";
import {Select as SelectField, GridRow} from 'ui';
import DynamicFields from "@/components/dynamic-fields.vue";

const model = defineModel();

const fieldApi = computed(() => ({
  action: 'sholokhov:exchange.EntityController.getByType',
  data: {code: 'source'},
  callback: normalizeTypeResponse,
}));

const normalizeTypeResponse = (response) => {
  if (Array.isArray(response.data)) {
    response.data = response.data.map(type => ({
      value: type.entity,
      name: type.name,
    }));
  }

  return response;
}
</script>

<template>
  <GridRow>
    <template #title>{{ getMessage('SHOLOKHOV_EXCHANGE_DETAIL_ENTITY_UI_SOURCE_TITLE_FIELD_TYPE') }}</template>
    <template #content>
      <SelectField v-model="model.type" :api="fieldApi" name="type" />
    </template>
  </GridRow>

  <DynamicFields v-model="model" :type="model.type" />
</template>