<template>
    <keep-alive>
        <component :is="active ? 'slot' : null">
            <slot />
        </component>
    </keep-alive>
</template>

<script>
import Slot from './Slot.vue';

export default {
    props: {
        label: {},
        implicit_label: { default: false }
    },
    data () {
        return {
            active: true
        };
    },
    created () {
        this.$parent.$parent.addTab(this);
    },
    components: {
        Slot
    }
};
</script>
