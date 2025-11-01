<template>
  <GridRow>
    <template #content>
      <div style="text-align: right; cursor: pointer" @click="emit('remove')">X</div>
    </template>
  </GridRow>

  <GridRow>
    <template #title>Тип свойства:</template>
    <template #content>
      <select v-model="model.type">
        <option v-for="template in templates" :key="template" :value="template.entity">{{ template.name }}</option>
      </select>
    </template>
  </GridRow>

  <DynamicFields
      v-if="model.type"
      v-model="model"
      :type="model.type"
      :target="target"
  />
</template>

<script setup>
import {GridRow} from "ui";
import {defineModel, defineProps, watch, defineEmits} from 'vue';
import DynamicFields from "@/components/dynamic-fields.vue";

const emit = defineEmits(['remove']);
const model = defineModel({default: {}});

defineProps({
  templates: {type: Array, default: () => []},
  target: {type: Object, required: true}
});

watch(
    () => data.template,
    () => model.to = '',
)
</script>