<template>
    <dl>
        <dt class="kint-parent" :class="{'expanded': expanded, 'locked': l}" @click="toggleExpanded">
            <span v-if="ap !== null" class="kint-access-path-trigger" title="Show access path" @click.stop="toggleAccessPath">&rlarr;</span>
            <span v-if="hasChildren" class="kint-popup-trigger" title="Open in new window" @click.stop="newWindow">&rarr;</span>
            <nav v-if="hasChildren" @click.double.stop="toggleChildren"></nav>
            <slot name="h" />
            <div v-if="showAccessPath" class="access-path" @click.stop="selectAp">{{ap}}</div>
        </dt>
        <keep-alive>
            <component :is="expanded ? 'fold' : null">
                <ul v-if="showTabs" class="kint-tabs">
                    <li v-for="tab in tabs" @click="activateTab(tab)">{{tab.label}}</li>
                </ul>
                <slot />
            </component>
        </keep-alive>
    </dl>
</template>

<script>
import Fold from './Fold.vue';

export default {
    data () {
        return {
            expanded: true,
            activeTab: null,
            tabs: [],
            showAccessPath: false
        };
    },
    computed: {
        hasChildren () {
            return this.tabs.length || this.l;
        },
        showTabs () {
            return this.tabs.length && !(this.tabs.length === 1 && this.tabs[0].implicit_label);
        }
    },
    props: {
        ap: { default: null }, // Access path
        e: { default: false }, // Expanded
        l: { default: false } // Locked
    },
    methods: {
        addTab (tab) {
            if (this.tabs.length === 0) {
                this.activeTab = tab;
            } else {
                tab.active = false;
            }

            this.tabs.push(tab);
        },
        activateTab (tab) {
            this.activeTab.active = false;
            tab.active = true;
            this.activeTab = tab;
        },
        toggleExpanded () {
            if (this.tabs.length) {
                this.expanded = !this.expanded;
            }
        },
        toggleAccessPath () {
            this.showAccessPath = !this.showAccessPath;
        },
        toggleChildren (e) {
            console.log(e);
        },
        selectAp (e) {
            var selection = window.getSelection();
            var range = document.createRange();

            range.selectNodeContents(e.target.lastChild);
            range.setStart(e.target.firstChild, 0);
            selection.removeAllRanges();
            selection.addRange(range);
        },
        newWindow () {
            // TODO
        }
    },
    mounted () {
        this.expanded = !!(this.e | 0);
    },
    components: {
        Fold
    }
};
</script>
