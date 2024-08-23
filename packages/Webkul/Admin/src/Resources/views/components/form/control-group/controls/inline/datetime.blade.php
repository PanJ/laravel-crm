<v-inline-datetime-edit {{ $attributes }}>
    <div class="group w-full max-w-full hover:rounded-sm">
        <div class="rounded-xs flex h-[34px] items-center ltr:pl-2.5 ltr:text-left rtl:pr-2.5 rtl:text-right">
            <div class="shimmer h-5 w-48 rounded border border-transparent"></div>
        </div>
    </div>
</v-inline-datetime-edit>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-inline-datetime-edit-template"
    >
        <div class="group w-full max-w-full hover:rounded-sm">
            <!-- Non-editing view -->
            <div
                v-if="! isEditing"
                class="rounded-xs flex h-[34px] items-center"
                :class="allowEdit ? 'hover:bg-gray-50 dark:hover:bg-gray-800' : ''"
                :style="textPositionStyle"
            >
                <x-admin::form.control-group.control
                    type="hidden"
                    ::id="name"
                    ::name="name"
                    v-model="inputValue"
                />

                <div class="group relative flex !w-full flex-col items-center">
                    <span class="w-40 truncate rounded border border-transparent ltr:pl-0.5 rtl:pr-0.5">@{{ inputValue }}</span>

                    <div
                        class="absolute bottom-0 mb-5 hidden flex-col items-center group-hover:flex"
                        v-if="inputValue.length > 20"
                    >
                        <span class="whitespace-no-wrap relative z-10 rounded-md bg-black p-4 text-xs leading-none text-white shadow-lg">
                            @{{ inputValue }}
                        </span>

                        <div class="-mt-2 h-3 w-3 rotate-45 bg-black"></div>
                    </div>
                </div>

                <template v-if="allowEdit">
                    <i
                        @click="toggle"
                        class="icon-edit cursor-pointer rounded text-2xl opacity-0 hover:bg-gray-200 group-hover:opacity-100 dark:hover:bg-gray-950"
                    ></i>
                </template>
            </div>
        
            <!-- Editing view -->
            <div
                class="relative flex w-full flex-col"
                v-else
            >
                <div class="relative flex w-full flex-col">
                    <x-admin::form.control-group.control
                        type="datetime"
                        ::id="name"
                        ::name="name"
                        class="text-normal py-1 ltr:pr-16 rtl:pl-16"
                        ::rules="rules"
                        ::label="label"
                        ::placeholder="placeholder"
                        ::style="inputPositionStyle"
                        v-model="inputValue"
                        ref="input"
                        readonly
                    />
                        
                    <!-- Action Buttons -->
                    <div class="absolute top-1/2 flex -translate-y-1/2 transform gap-0.5 bg-white ltr:right-2 rtl:left-2">
                        <button
                            type="button"
                            class="flex items-center justify-center bg-green-100 p-1 hover:bg-green-200 ltr:rounded-l-md rtl:rounded-r-md"
                            @click="save"
                        >
                            <i class="icon-tick text-md cursor-pointer font-bold text-green-600" />
                        </button>
                    
                        <button
                            type="button"
                            class="flex items-center justify-center bg-red-100 p-1 hover:bg-red-200 ltr:rounded-r-md rtl:rounded-l-md"
                            @click="cancel"
                        >
                            <i class="icon-cross-large text-md cursor-pointer font-bold text-red-600" />
                        </button>
                    </div>
                </div>

                <x-admin::form.control-group.error ::name="name"/>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-inline-datetime-edit', {
            template: '#v-inline-datetime-edit-template',

            emits: ['on-change', 'on-cancelled'],

            props: {
                name: {
                    type: String,
                    required: true,
                },

                value: {
                    required: true,
                },

                rules: {
                    type: String,
                    default: '',
                },

                label: {
                    type: String,
                    default: '',
                },

                placeholder: {
                    type: String,
                    default: '',
                },

                position: {
                    type: String,
                    default: 'right',
                },

                allowEdit: {
                    type: Boolean,
                    default: true,
                },

                errors: {
                    type: Object,
                    default: {},
                },

                url: {
                    type: String,
                    default: '',
                },
            },

            data() {
                return {
                    inputValue: this.value,

                    isEditing: false,

                    isRTL: document.documentElement.dir === 'rtl',
                };
            },

            watch: {
                /**
                 * Watch the value prop.
                 * 
                 * @param {String} newValue 
                 */
                value(newValue) {
                    this.inputValue = newValue;
                },
            },

            computed: {
                /**
                 * Get the input position style.
                 * 
                 * @return {String}
                 */
                 inputPositionStyle() {
                    return this.position === 'left' 
                        ? this.isRTL 
                            ? 'text-align: right; padding-right: 9px;' 
                            : 'text-align: left; padding-left: 9px;'
                        : this.isRTL 
                            ? 'text-align: left; padding-left: 9px;' 
                            : 'text-align: right; padding-right: 9px;';
                },

                /**
                 * Get the text position style.
                 * 
                 * @return {String}
                 */
                textPositionStyle() {
                    return this.position === 'left'  ? this.isRTL 
                            ? 'justify-content: end;' 
                            : 'justify-content: space-between;' 
                        : this.isRTL 
                            ? 'justify-content: space-between;' 
                            : 'justify-content: end;';
                },
            },

            methods: {
                /**
                 * Toggle the input.
                 * 
                 * @return {void}
                 */
                toggle() {
                    this.isEditing = true;
                },

                /**
                 * Save the input value.
                 * 
                 * @return {void}
                 */
                save() {
                    if (this.errors[this.name]) {
                        return;
                    }

                    this.isEditing = false;

                    if (this.url) {
                        this.$axios.put(this.url, {
                                [this.name]: this.inputValue,
                            })
                            .then((response) => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch((error) => {
                                this.inputValue = this.value;
                            });                        
                    }

                    this.$emit('on-change', {
                        name: this.name,
                        value: this.inputValue,
                    });
                },

                /**
                 * Cancel the input value.
                 * 
                 * @return {void}
                 */
                cancel() {
                    this.inputValue = this.value;

                    this.isEditing = false;

                    this.$emit('on-cancelled', {
                        name: this.name,
                        value: this.inputValue,
                    });
                },
            },
        });
    </script>
@endPushOnce