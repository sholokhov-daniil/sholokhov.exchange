<template>
  <GridRow>
    <template #title>Свойство в которое записывается значение</template>
    <template #content>
      <EntitySelector
          v-model="model.to"
          :options="toOptions"
      />
    </template>
  </GridRow>

  <GridRow>
    <template #title>Откуда брать значение</template>
    <template #content>
      <Input v-model="model.from" />
    </template>
  </GridRow>

  <GridRow>
    <template #title>Ключевое значение</template>
    <template #content>
      <CheckBox v-model="model.pripary" />
    </template>
  </GridRow>

  <GridRow>
    <template #title>Хранит хеш импорта</template>
    <template #content>
      <CheckBox v-model="model.hash" />
    </template>
  </GridRow>

  <GridRow>
    <template #title>Создавать значение при его отсутствии</template>
    <template #content>
      <CheckBox v-model="model.isCreatedLink" />
    </template>
  </GridRow>
</template>

<script setup>
import {defineModel, defineProps, computed} from 'vue';
import {GridRow, Input, CheckBox, EntitySelector} from "ui";

const model = defineModel({});

const props = defineProps({
  target: {type: Object, required: true},
});

const toOptions = computed(
    () => (
        {
          multiple: false,
          dialogOptions: {
            entities: [
              {
                id: 'sholokhov-exchange-user-field',
                dynamicSearch: true,
                dynamicLoad: true,
                options: {
                  entityId: `HLBLOCK_${props.target.entityId}`
                }
              }
            ]
          }
        }
    )
)
</script>