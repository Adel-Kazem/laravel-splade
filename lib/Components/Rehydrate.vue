<template>
  <Render
    v-if="html"
    :html="html"
    :passthrough="passthrough"
  />
  <!--
    Only swap to the placeholder WHILE LOADING if one was actually provided.
    Upstream fell to an empty `placeholder` slot on every load, so the FIRST
    rehydrate (html still null) unmounted the whole section mid-fetch — the page
    collapsed and the scroll position was lost (later refreshes looked seamless
    only because `html` was non-null, keeping the old content mounted). Guarding
    on `$slots.placeholder` keeps the current content mounted when no placeholder
    is given, so the section never blanks.
  -->
  <slot
    v-else-if="loading && $slots.placeholder"
    name="placeholder"
  />
  <slot v-else />
</template>

<script>
import { Splade } from "../Splade.js";
import Render from "./Render.vue";

export default {
    components: { Render },
    props: {
        name: {
            type: String,
            required: true
        },

        on: {
            type: Array,
            required: true
        },

        url: {
            type: String,
            required: false,
            default() {
                return Splade.isSsr ? "" : window.location.href;
            },
        },

        poll: {
            type: Number,
            required: false,
            default: null,
        },

        passthrough: {
            type: Object,
            required: false,
            default() {
                return {};
            },
        }
    },

    emits: ["loaded"],

    data() {
        return {
            html: null,
            loading: false
        };
    },

    mounted() {
        this.on.forEach((eventName) => {
            this.$splade.on(eventName, this.request);
        });

        if (this.poll) {
            setTimeout(() => {
                this.request();
            }, this.poll);
        }
    },

    methods: {
        async request() {
            this.loading = true;

            // Preserve the scroll position across the content swap — a live
            // rehydrate shouldn't yank the user away from where they were.
            const previousScroll = Splade.isSsr ? 0 : window.scrollY;

            Splade.rehydrate(this.url, this.name).then((response) => {
                this.html = response.data.html;
                this.loading = false;
                this.$emit("loaded");

                if (!Splade.isSsr) {
                    this.$nextTick(() => window.scrollTo(0, previousScroll));
                }

                if (this.poll) {
                    setTimeout(() => {
                        this.request();
                    }, this.poll);
                }
            });
        },
    }
};
</script>
