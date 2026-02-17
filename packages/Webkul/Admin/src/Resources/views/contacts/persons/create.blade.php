<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('admin::app.contacts.persons.create.title')
    </x-slot>

    {!! view_render_event('admin.persons.create.form.before') !!}

    <!--Create Page Form -->
    <x-admin::form
        :action="route('admin.contacts.persons.store')"
        enctype="multipart/form-data"
    >
        <div class="flex flex-col gap-4">
            <!-- Header -->
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.persons.create.breadcrumbs.before') !!}

                    <!-- Breadcrumb -->
                    <x-admin::breadcrumbs name="contacts.persons.create" />

                    {!! view_render_event('admin.persons.create.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.contacts.persons.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.persons.create.create_button.before') !!}

                        <!-- Create button for Person -->
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.contacts.persons.create.save-btn')
                        </button>

                        {!! view_render_event('admin.persons.create.create_button.after') !!}
                    </div>
                </div>
            </div>

            <!-- Form fields -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                {!! view_render_event('admin.persons.create.form_controls.before') !!}

                <v-contact-type-toggle>
                    <x-admin::attributes
                        :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                            ['code', 'NOTIN', ['organization_id']],
                            'entity_type' => 'persons',
                        ])"
                        :custom-validations="[
                            'name' => [
                                'min:2',
                                'max:100',
                            ],
                            'job_title' => [
                                'max:100',
                            ],
                        ]"
                    />
                </v-contact-type-toggle>

                <v-organization></v-organization>

                {!! view_render_event('admin.persons.create.form_controls.after') !!}
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.persons.create.form.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-organization-template"
        >
            <div>
                <x-admin::attributes
                    :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                        ['code', 'IN', ['organization_id']],
                        'entity_type' => 'persons',
                    ])"
                />

                <template v-if="organizationName">
                    <x-admin::form.control-group.control
                        type="hidden"
                        name="organization_name"
                        v-model="organizationName"
                    />
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-organization', {
                template: '#v-organization-template',

                data() {
                    return {
                        organizationName: null,
                    };
                },

                methods: {
                    handleLookupAdded(event) {
                        this.organizationName = event?.name || null;
                    },
                },
            });
        </script>
    @endPushOnce

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-contact-type-toggle-template"
        >
            <div><slot></slot></div>
        </script>

        <script type="module">
            app.component('v-contact-type-toggle', {
                template: '#v-contact-type-toggle-template',

                data() {
                    return {
                        individualFields: ['ssn', 'date_of_birth', 'filing_status', 'occupation'],
                        businessFields: ['business_name', 'ein', 'entity_type_tax', 'fiscal_year_end'],
                        individualOptionValue: null,
                        businessOptionValue: null,
                    };
                },

                mounted() {
                    this.$nextTick(() => {
                        this.initContactTypeToggle();
                    });
                },

                methods: {
                    initContactTypeToggle() {
                        const select = document.querySelector('[data-attribute-code="contact_type"] select, select[name="contact_type"]');

                        if (!select) return;

                        // Determine option values by matching option text
                        Array.from(select.options).forEach(option => {
                            const text = option.textContent.trim();
                            if (text === 'Individual') this.individualOptionValue = option.value;
                            if (text === 'Business') this.businessOptionValue = option.value;
                        });

                        // Hide all type-specific fields initially
                        this.hideFields([...this.individualFields, ...this.businessFields]);

                        // Show correct fields based on current selection (important for edit page)
                        this.toggleFields(select.value);

                        // Listen for changes
                        select.addEventListener('change', (e) => {
                            this.toggleFields(e.target.value);
                        });
                    },

                    toggleFields(selectedValue) {
                        if (selectedValue === this.individualOptionValue) {
                            this.showFields(this.individualFields);
                            this.hideFields(this.businessFields);
                        } else if (selectedValue === this.businessOptionValue) {
                            this.showFields(this.businessFields);
                            this.hideFields(this.individualFields);
                        } else {
                            // No selection -- hide all type-specific fields
                            this.hideFields([...this.individualFields, ...this.businessFields]);
                        }
                    },

                    showFields(codes) {
                        codes.forEach(code => {
                            const el = document.querySelector(`[data-attribute-code="${code}"]`);
                            if (el) el.style.display = '';
                        });
                    },

                    hideFields(codes) {
                        codes.forEach(code => {
                            const el = document.querySelector(`[data-attribute-code="${code}"]`);
                            if (el) el.style.display = 'none';
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
