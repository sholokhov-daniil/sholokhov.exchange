<template v-if="type">
  <Component
      v-if="view"
      :is="view"
      v-model="model"
      v-bind="attr" />
  <div v-else ref="externalContainerRef">
  </div>
</template>

<script setup>
import {computed, useAttrs} from 'vue';
import {defineModel, defineProps} from 'vue';
import {internalTargetField} from "@/view/factory";

const attr = useAttrs();
const model = defineModel({default: {}});
const props = defineProps({
  type: {type: String, required: true}
});

const view = computed(() => internalTargetField(props.type));
</script>