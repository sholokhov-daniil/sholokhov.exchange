<template>
  <GridRow>
    <template #content>
      Значение: {{ model }}<br><br>
      Тип: {{ target }}<br><br>
      Шаблоны: {{ data.templates }} <br><br>

      <button v-if="target?.type" type="button" @click="add">Добавить</button>
      <Alert v-else v-for="message in data.errors" :key="message" type="danger">
        {{ message }}
      </Alert>
    </template>
  </GridRow>

  <template v-for="(field, index) in model" :key="index">
    <MapFieldSelector
        v-model="model[index]"
        :target="target"
        :templates="templates"
        @remove="model.splice(index, 1)"
    />

    <GridRow class="row-split" ></GridRow>
  </template>
</template>

<script setup>
import {defineModel, defineProps, reactive, watch, onMounted, computed} from 'vue';
import {Alert, GridRow} from "ui";
import {runAction} from "utils";
import MapFieldSelector from "@/components/map-field-selector.vue";

const model = defineModel({default: []});

const props = defineProps({
  target: {type: Object, default: () => ({})}
});

const data = reactive({
  templates: {},
  errors: [],
});

onMounted(() => {
  if (!props.target?.type) {
    showEmptyError();
  }
})

const templates = computed(() => data.templates[props.target?.type] || []);

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

      if (data.templates[newValue]) {
        return;
      }

      data.templates[newValue] = {};
      loadTemplates(newValue)
          .catch(response => data.errors = response.errors.map(error => error.message))
    }
)

const showEmptyError = () => data.errors = ["Необходимо выбрать тип обмена"];

const loadTemplates = async (target) => {
  const response = await runAction('sholokhov:exchange.MapController.getTemplates', {target: target});

  if (!Array.isArray(response.data)) {
    data.errors.push('Ошибка загрузки данных');
    return;
  }

  data.templates[target] = [];

  response.data.forEach(map => data.templates[target].push(map || {}));
}

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