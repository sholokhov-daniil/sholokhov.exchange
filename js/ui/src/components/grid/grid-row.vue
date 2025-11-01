<template>
  <RowTable v-bind="$attrs">
    <ColumnTitleTable v-bind="titleAttrs">
      <slot name="title"></slot>
    </ColumnTitleTable>

    <ColumnValueTable v-bind="valueAttrs">
      <slot name="content"></slot>
    </ColumnValueTable>
  </RowTable>
</template>

<script setup>
import ColumnTitleTable from "../table/column-title-table.vue";
import ColumnValueTable from "../table/column-value-table.vue";
import RowTable from "../table/row-table.vue";

defineProps({
  titleAttrs: {type: Object, default: ({})},
  valueAttrs: {type: Object, default: ({})},
});
</script>