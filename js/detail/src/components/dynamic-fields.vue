<template v-if="type">
  <Component
      v-if="view"
      :is="view"
      v-model="model"
      :target="target"
      v-bind="attr" />
  <div v-else ref="externalContainerRef">
  </div>
</template>

<script setup>
import {computed, ref, watch, useAttrs} from 'vue';
import {defineModel, defineProps} from 'vue';
import {internalFieldView} from "@/view/factory";
import {view as ExternalView} from "@/view";

const attr = useAttrs();
const model = defineModel({default: {}});
const props = defineProps({
  target: {type: Object, required: true}
});

const externalContainerRef = ref();

const view = computed(() => internalFieldView(props.target.type));
watch(
    () => props.target.type,
    (newValue) => {
      if (!newValue || view.value) {
        return;
      }

      ExternalView(
          props.target,
          {
            container: externalContainerRef,
            data: model,
            ...attr
          }
      );
    }
)
</script>