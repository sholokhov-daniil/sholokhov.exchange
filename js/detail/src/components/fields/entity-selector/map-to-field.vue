<template>
  <EntitySelector
      v-model="model"
      :options="data.options"
  />
</template>

<script setup>
import {EntitySelector} from "ui";
import {runAction} from "utils";
import {defineModel, defineProps, watch, reactive, onMounted} from "vue";

const model = defineModel({});
const props = defineProps({
  target: {type: Object, required: true},
  type: {type: String, required: true},
});

const data = reactive({
  options: {}
});

onMounted(() => {
  query(props.target?.type, props.target?.entityId, props.type)
})

watch(
    () => props.target.entityId,
    (newValue) => query(props.target?.type || '', newValue, props.type)
);

watch(
    () => props.target.type,
    (newValue) => query(newValue, props.target?.entityId || '', props.type)
);

watch(
    () => props.type,
    (newValue) => query(props.target?.type || '', protps.target?.entityId || '', newValue)
)

const query = (target, entityID, type) => {
  runAction(
      'sholokhov:exchange.MapController.getToSelectorOptions',
      {
        target: target,
        entityId: entityID,
        type: type
      }
  )
      .then(response => data.options = response.data)
      .catch(response => console.log(response))
}
</script>