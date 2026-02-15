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

                @if(request()->query('group') === 'telegram')
                <button
                    type="button"
                    class="secondary-button ml-2"
                    @click="testTelegramConnection"
                    :disabled="testingConnection"
                >
                    <span v-if="!testingConnection">@lang('admin::app.configuration.index.telegram.settings.test-connection')</span>
                    <span v-else>Testing...</span>
                </button>

                <div v-if="testResult" class="mt-2 p-2 rounded" :class="testResult.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    @{{ testResult.message }}
                </div>
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
</x-admin::layouts>
