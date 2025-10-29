import { defineAsyncComponent } from 'vue';

export const internalView = (template) => {
    switch (template) {
        case 'field.base':
            return defineAsyncComponent(() => import('@/components/map/base-field.vue'));
        case 'source.xml.db':
            return defineAsyncComponent(() => import('@/components/source/bd-xml.vue'));
        case 'source.iblock.element':
            return  defineAsyncComponent(() => import('@/components/source/iblock-element.vue'));
        case 'source.csv':
            return defineAsyncComponent(() => import('@/components/source/simple-csv.vue'));
        case 'source.json.file':
            return defineAsyncComponent(() => import('@/components/source/simple-json-file.vue'));
        case 'source.xml.simple':
            return defineAsyncComponent(() => import('@/components/source/simple-xml.vue'));
        case 'target.import.hl.element':
            return defineAsyncComponent(() => import('@/components/target/hl-element.vue'));
        case 'target.import.iblock.element':
            return defineAsyncComponent(() => import('@/components/target/iblock-element.vue'));
        case 'target.import.catalog.product.simple':
            return defineAsyncComponent(() => import('@/components/target/iblock-simple-product.vue'))
        case 'target.import.iblock.props.enum':
            return defineAsyncComponent(() => import('@/components/target/iblock-property-enum.vue'));
        case 'taregt.import.iblock.section':
            return defineAsyncComponent(() => import('@/components/target/iblock-section.vue'));
        case 'target.import.uf.enum':
            return defineAsyncComponent(() => import('@/components/target/uf-enum-value.vue'));
        default:
            return null;
    }
}