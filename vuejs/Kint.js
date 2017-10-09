import Vue from 'vue';
import Obj from './Obj.vue';
import Tab from './Tab.vue';

Vue.component('kv-obj', Obj);
Vue.component('kv-tab', Tab);

export default {
    Vue: Vue,
    instances: [],
    init (el) {
        this.instances.push(new this.Vue({
            el: el
        }));
    }
};
