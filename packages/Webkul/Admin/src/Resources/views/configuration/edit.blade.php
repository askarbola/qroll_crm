@php
    $activeConfiguration = system_config()->getActiveConfigurationItem();

    $name = $activeConfiguration->getName();
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ strip_tags($name) }}
    </x-slot>

    {!! view_render_event('admin.configuration.edit.form_controls.before') !!}

    <!-- Configuration form fields -->
    <x-admin::form
        action=""
        enctype="multipart/form-data"
    >
        <!-- Save Inventory -->
        <div class="mt-3.5 flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ $name }}
            </p>

            <!-- Save Inventory -->
            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('admin.configuration.edit.back_button.before') !!}

                <!-- Back Button -->
                <a
                    href="{{ route('admin.configuration.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.configuration.index.back')
                </a>

                {!! view_render_event('admin.configuration.edit.back_button.after') !!}

                {!! view_render_event('admin.configuration.edit.save_button.before') !!}

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.configuration.index.save-btn')
                </button>

                @if(request()->route('slug') === 'telegram')
                    <v-telegram-test-button></v-telegram-test-button>
                @endif

                {!! view_render_event('admin.configuration.edit.save_button.after') !!}
            </div>
        </div>

        <div class="grid grid-cols-[1fr_2fr] gap-10 max-lg:grid-cols-1 max-lg:gap-4 lg:mt-6">
            @foreach ($activeConfiguration->getChildren() as $child)
                <div class="grid content-start gap-2.5 max-lg:mt-6">
                    <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                        {{ $child->getName() }}
                    </p>

                    <p class="leading-[140%] text-gray-600 dark:text-gray-300">
                        {!! $child->getInfo() !!}
                    </p>
                </div>

                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    {!! view_render_event('admin.configuration.edit.form_controls.before') !!}

                    @foreach ($child->getFields() as $field)
                        @if (
                            $field->getType() == 'blade'
                            && view()->exists($path = $field->getPath())
                        )
                            {!! view($path, compact('field', 'child'))->render() !!}
                        @else 
                            @include ('admin::configuration.field-type')
                        @endif
                    @endforeach

                    {!! view_render_event('admin.configuration.edit.form_controls.after') !!}
                </div>
            @endforeach
        </div>
    </x-admin::form>

    {!! view_render_event('admin.configuration.edit.form_controls.after') !!}

    @if(request()->route('slug') === 'telegram')
        @pushOnce('scripts')
            <script type="text/x-template" id="v-telegram-test-button-template">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="secondary-button"
                        @@click="testConnection"
                        :disabled="testing"
                    >
                        <span v-if="!testing">@lang('admin::app.configuration.index.telegram.settings.test-connection')</span>
                        <span v-else>Testing...</span>
                    </button>

                    <div
                        v-if="result"
                        class="p-2 rounded text-sm"
                        :class="result.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                        @{{ result.message }}
                    </div>
                </div>
            </script>

            <script type="module">
                app.component('v-telegram-test-button', {
                    template: '#v-telegram-test-button-template',

                    data() {
                        return {
                            testing: false,
                            result: null,
                        };
                    },

                    methods: {
                        async testConnection() {
                            const botToken = document.querySelector('input[name="telegram[settings][connection][bot_token]"]')?.value;
                            const chatId = document.querySelector('input[name="telegram[settings][connection][chat_id]"]')?.value;

                            if (!botToken || !chatId) {
                                this.result = { success: false, message: 'Please enter both Bot Token and Group Chat ID' };
                                return;
                            }

                            this.testing = true;
                            this.result = null;

                            try {
                                const response = await this.$axios.post(
                                    '{{ route("admin.configuration.telegram.test") }}',
                                    { bot_token: botToken, chat_id: chatId }
                                );

                                this.result = response.data;
                            } catch (error) {
                                this.result = {
                                    success: false,
                                    message: error.response?.data?.message || 'Connection test failed',
                                };
                            } finally {
                                this.testing = false;
                            }
                        },
                    },
                });
            </script>
        @endPushOnce
    @endif
</x-admin::layouts>
