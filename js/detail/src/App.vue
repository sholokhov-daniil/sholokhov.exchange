<template>
  <Teleport v-if="teleport.general" :to="teleport.general">
    <GeneralBlock v-model="data.form.general" />
  </Teleport>

  <Teleport v-if="teleport.target" :to="teleport.target">
    <TargetBlock v-model="data.form.target" />
  </Teleport>

  <Teleport v-if="teleport.source" :to="teleport.source">
    <SourceBlock v-model="data.form.source" />
  </Teleport>

  <Teleport v-if="teleport.map" :to="teleport.map">
    <MapBlock v-model="data.form.map" :target="data.form?.target" />
  </Teleport>

  <Teleport to='#sholokhov-button-pannel'>
    <div class="adm-detail-content-btns-wrap" style="left: 0px;">
      <div class="adm-detail-content-btns">
        <button type="button" class="ui-btn ui-btn-success" @click="save">Сохранить</button>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import {defineProps, reactive, onMounted} from 'vue';
import GeneralBlock from "@/components/general-block.vue";
import TargetBlock from "@/components/target-block.vue";
import SourceBlock from "@/components/source-block.vue";
import MapBlock from "@/components/map-block.vue";
import {runAction} from "utils";

defineProps({
  teleport: {type: Object, required: true},
  formContainer: {type: String, required: true},
  id: {type: Number, default: () => 0},
  signed: {type: String, required: false, default: () => ''},
});

const data = reactive({
  form: {
    general: {},
    target: {},
    source: {},
    map: [],
  },
});

onMounted(() => {
  // for(let target in props.fields) {
  //   data.form[target] = {};
  // }
});

function save() {
  // BX.adminPanel.closeWait()

  runAction('sholokhov:exchange.SettingsController.create', {fields: data.form})
      .then(showSuccess)
      .catch(showErrors);
}

function showSuccess() {
  const popup = createPopup();
  popup.setTitleBar('Сохранение настроек');
  popup.setContent('Настройки сохранены');
  popup.subscribeFromOptions({
    onClose: () => {
      window.location.href = `/bitrix/admin/sholokhov_exchange_detail.php?id=${data.form.general.hash}`
    }
  });
  popup.show();
}

function showErrors(response) {
  let text = '<div>Ошибка сохранения:</div>';
  response.errors.forEach(error => text += `<div style="color: red">${error.message}</div>`)

  const popup = createPopup();
  popup.setTitleBar("При сохранении возникла ошибка:");
  popup.setContent(text);
  popup.show();
}

function createPopup() {
  return new BX.Main.Popup({
    width: 800,
    autoHide: true,
    titleBar: ' ',
    offsetTop: 0,
    offsetLeft: 0,
    closeIcon: true,
    closeByEsc: true,
    zIndex: 2000,
    minWidth: 250,
    maxWidth: 450,
    background: "#fff",
    borderWidth: 1,
    contentBackground: "transparent",
    animation: "fading-slide",
    buttons: [
      new BX.PopupWindowButton({
        text: "Закрыть",
        events: {
          click: function() {
            this.popupWindow.close();
          }
        }
      }),
    ]
  });
}
</script>