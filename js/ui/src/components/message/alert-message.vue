<template>
  <div class="ui-alert" :class="[alertSize, alertType, alertIcon, alertCloseButton]">
    <span class="ui-alert-message">
      <strong v-if="alert">Внимание!</strong>
      <slot></slot>
    </span>
    <span v-if="alertCloseButton" class="ui-alert-close-btn"></span>
  </div>
</template>

<script setup>
import {defineProps, onMounted, computed, ref} from 'vue';

const props = defineProps({
  size: {type: String, default: () => ''},
  type: {type: String, default: () => ''},
  icon: {type: String, default: () => ''},
  alert: {type: Boolean, default: () => true},
  closeButton: {type: Boolean, default: () => true},
  centerText: {type: Boolean, default: () => false},
})

const alertSize = computed(() => props.size === 'sx' ? 'ui-alert-xs' : 'ui-alert-md');

const alertType = computed(() => {
  let type;

  switch (props.type) {
    case 'success':
      type = 'ui-alert-success';
      break;
    case 'danger':
      type = 'ui-alert-danger';
      break;
    case 'warning':
      type = 'ui-alert-warning';
      break;
    case 'primary':
      type = 'ui-alert-primary';
      break;
    default:
      type = 'ui-alert-default';
      break;
  }

  return type;
});

const alertIcon = computed(() => {
  let icon;

  switch (props.icon) {
    case 'warning':
      icon = 'ui-alert-icon-warning';
      break;
    case 'danger':
      icon = 'ui-alert-icon-danger';
      break;
    case 'info':
      icon = 'ui-alert-icon-info';
      break;
  }

  return icon;
});

const alertCloseButton = computed(() => props.closeButton ? 'ui-alert-close-animate' : '');

onMounted(() => {
  BX.loadExt('ui.alerts');
})
</script>