{!! view_render_event('admin.contacts.persons.view.attributes.before', ['person' => $person]) !!}

<div class="flex w-full flex-col gap-4 border-b border-gray-200 p-4 dark:border-gray-800">
    <x-admin::accordion class="select-none !border-none">
        <x-slot:header class="!p-0">
            <h4 class="font-semibold dark:text-white">
                @lang('admin::app.contacts.persons.view.about-person')
            </h4>
        </x-slot>

        <x-slot:content class="mt-4 !px-0 !pb-0">
            {!! view_render_event('admin.contacts.persons.view.attributes.form_controls.before', ['person' => $person]) !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, () => {})">
                    {!! view_render_event('admin.contacts.persons.view.attributes.form_controls.attributes_view.before', ['person' => $person]) !!}
        
                    <x-admin::attributes.view
                        :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                            'entity_type' => 'persons',
                            ['code', 'NOTIN', ['name']]
                        ])"
                        :entity="$person"
                        :url="route('admin.contacts.persons.update', $person->id)"
                        :allow-edit="true"
                    />
        
                    {!! view_render_event('admin.contacts.persons.view.attributes.form_controls.attributes_view.after', ['person' => $person]) !!}
                </form>
            </x-admin::form>
        
            {!! view_render_event('admin.contacts.persons.view.attributes.form_controls.after', ['person' => $person]) !!}
        </x-slot>
    </x-admin::accordion>
</div>

@pushOnce('scripts')
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            const individualFields = ['ssn', 'date_of_birth', 'filing_status', 'occupation'];
            const businessFields = ['business_name', 'ein', 'entity_type_tax', 'fiscal_year_end'];

            // Find the contact_type display value
            const contactTypeRow = document.querySelector('[data-attribute-code="contact_type"]');

            if (!contactTypeRow) return;

            // The view component renders as: <div class="grid grid-cols-[1fr_2fr]"><div>label</div><div class="font-medium">value</div></div>
            const valueCell = contactTypeRow.querySelector('.font-medium');
            const contactTypeText = valueCell ? valueCell.textContent.trim() : '';

            function hideFields(codes) {
                codes.forEach(function(code) {
                    const el = document.querySelector('[data-attribute-code="' + code + '"]');
                    if (el) el.style.display = 'none';
                });
            }

            if (contactTypeText === 'Individual' || contactTypeText.includes('Individual')) {
                hideFields(businessFields);
            } else if (contactTypeText === 'Business' || contactTypeText.includes('Business')) {
                hideFields(individualFields);
            } else {
                // No contact type -- hide all type-specific
                hideFields([...individualFields, ...businessFields]);
            }
        });
    </script>
@endPushOnce

{!! view_render_event('admin.contacts.persons.view.attributes.before', ['person' => $person]) !!}
