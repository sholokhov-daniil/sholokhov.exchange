import { defineAsyncComponent } from 'vue';

export const internalView = (template) => {
    switch (template) {
        case 'map_field':
            return defineAsyncComponent(() => import('@/components/map/base-field.vue'));
        case 'source_db_xml':
            return defineAsyncComponent(() => import('@/components/source/bd-xml.vue'));
        case 'source_iblock_element':
            return  defineAsyncComponent(() => import('@/components/source/iblock-element.vue'));
        case 'source_simple_csv':
            return defineAsyncComponent(() => import('@/components/source/simple-csv.vue'));
        case 'source_simple_json_file':
            return defineAsyncComponent(() => import('@/components/source/simple-json-file.vue'));
        case 'source_simple_xml':
            return defineAsyncComponent(() => import('@/components/source/simple-xml.vue'));
        case 'target_import_hl_element':
            return defineAsyncComponent(() => import('@/components/target/hl-element.vue'));
        case 'target_import_iblock_element':
            return defineAsyncComponent(() => import('@/components/target/iblock-element.vue'));
        case 'target_import_iblock_element_simple_product':
            return defineAsyncComponent(() => import('@/components/target/iblock-simple-product.vue'))
        case 'target_import_iblock_property_enum_value':
            return defineAsyncComponent(() => import('@/components/target/iblock-property-enum.vue'));
        case 'target_import_iblock_section':
            return defineAsyncComponent(() => import('@/components/target/iblock-section.vue'));
        case 'target_import_uf_enum_value':
            return defineAsyncComponent(() => import('@/components/target/uf-enum-value.vue'));
        default:
            return null;
    }
}