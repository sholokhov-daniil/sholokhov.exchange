<template>
  <GridRow>
    <template #content>
      Значение: {{ model }}<br><br>
      Тип: {{ target }}<br><br>

      <button v-if="target?.type" type="button" @click="add">Добавить</button>
      <Alert v-else v-for="message in data.errors" :key="message" type="danger">
        {{ message }}
      </Alert>
    </template>
  </GridRow>

  <template v-for="(field, index) in model" :key="index">
    <GridRow>
      <template #content>
        <div style="text-align: right; cursor: pointer" @click="model.splice(index, 1)">X</div>
      </template>
    </GridRow>
    <DynamicFields
        v-model="model[index]"
        :target="target"
    />

    <GridRow class="row-split" ></GridRow>
  </template>
</template>

<script setup>
import {defineModel, defineProps, reactive, watch, onMounted} from 'vue';
import {Alert, GridRow} from "ui";
import DynamicFields from "@/components/dynamic-fields.vue";

const model = defineModel({default: []});

const props = defineProps({
  target: {type: Object, default: () => ({})}
});

const data = reactive({
  errors: [],
});

onMounted(() => {
  if (!props.target?.type) {
    showEmptyError();
  }
})

watch(
    () => props.target.type,
    (newValue) => {
      if (model) {
        model.value = [];
      }

      if (!newValue) {
        showEmptyError();
        return;
      }

      data.errors = [];
    }
)

const showEmptyError = () => data.errors = ["Необходимо выбрать тип обмена"];

const add = () => {
  if (!Array.isArray(model.value)) {
    model.value = [];
  }

  model.value.unshift({});
}
</script>

<style scoped>
.row-split :deep(td) {
  border-bottom: 1px solid silver;
}
</style>