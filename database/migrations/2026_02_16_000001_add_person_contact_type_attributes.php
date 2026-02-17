<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $maxSortOrder = DB::table('attributes')
            ->where('entity_type', 'persons')
            ->max('sort_order') ?? 6;

        $textAttributes = [
            [
                'code'            => 'ssn',
                'name'            => 'SSN',
                'type'            => 'text',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 2,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'code'            => 'date_of_birth',
                'name'            => 'Date of Birth',
                'type'            => 'date',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 3,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'code'            => 'occupation',
                'name'            => 'Occupation',
                'type'            => 'text',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 5,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'code'            => 'business_name',
                'name'            => 'Business Name',
                'type'            => 'text',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 6,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'code'            => 'ein',
                'name'            => 'EIN',
                'type'            => 'text',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 7,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'code'            => 'fiscal_year_end',
                'name'            => 'Fiscal Year End',
                'type'            => 'text',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 9,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        // Insert non-select attributes (idempotent)
        foreach ($textAttributes as $attribute) {
            $exists = DB::table('attributes')
                ->where('code', $attribute['code'])
                ->where('entity_type', $attribute['entity_type'])
                ->exists();

            if (! $exists) {
                DB::table('attributes')->insert($attribute);
            }
        }

        // Insert select attributes with options (idempotent)

        // 1. Contact Type (sort_order: $maxSortOrder + 1)
        $exists = DB::table('attributes')
            ->where('code', 'contact_type')
            ->where('entity_type', 'persons')
            ->exists();

        if (! $exists) {
            $contactTypeId = DB::table('attributes')->insertGetId([
                'code'            => 'contact_type',
                'name'            => 'Contact Type',
                'type'            => 'select',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 1,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            DB::table('attribute_options')->insert([
                ['name' => 'Individual', 'attribute_id' => $contactTypeId, 'sort_order' => 1],
                ['name' => 'Business',   'attribute_id' => $contactTypeId, 'sort_order' => 2],
            ]);
        }

        // 2. Filing Status (sort_order: $maxSortOrder + 4)
        $exists = DB::table('attributes')
            ->where('code', 'filing_status')
            ->where('entity_type', 'persons')
            ->exists();

        if (! $exists) {
            $filingStatusId = DB::table('attributes')->insertGetId([
                'code'            => 'filing_status',
                'name'            => 'Filing Status',
                'type'            => 'select',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 4,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            DB::table('attribute_options')->insert([
                ['name' => 'Single',                    'attribute_id' => $filingStatusId, 'sort_order' => 1],
                ['name' => 'Married Filing Jointly',    'attribute_id' => $filingStatusId, 'sort_order' => 2],
                ['name' => 'Married Filing Separately', 'attribute_id' => $filingStatusId, 'sort_order' => 3],
                ['name' => 'Head of Household',         'attribute_id' => $filingStatusId, 'sort_order' => 4],
                ['name' => 'Qualifying Widow(er)',      'attribute_id' => $filingStatusId, 'sort_order' => 5],
            ]);
        }

        // 3. Entity Type (sort_order: $maxSortOrder + 8)
        $exists = DB::table('attributes')
            ->where('code', 'entity_type_tax')
            ->where('entity_type', 'persons')
            ->exists();

        if (! $exists) {
            $entityTypeTaxId = DB::table('attributes')->insertGetId([
                'code'            => 'entity_type_tax',
                'name'            => 'Entity Type',
                'type'            => 'select',
                'entity_type'     => 'persons',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => $maxSortOrder + 8,
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '1',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            DB::table('attribute_options')->insert([
                ['name' => 'LLC',                 'attribute_id' => $entityTypeTaxId, 'sort_order' => 1],
                ['name' => 'S-Corp',              'attribute_id' => $entityTypeTaxId, 'sort_order' => 2],
                ['name' => 'C-Corp',              'attribute_id' => $entityTypeTaxId, 'sort_order' => 3],
                ['name' => 'Partnership',         'attribute_id' => $entityTypeTaxId, 'sort_order' => 4],
                ['name' => 'Sole Proprietorship', 'attribute_id' => $entityTypeTaxId, 'sort_order' => 5],
                ['name' => 'Non-Profit',          'attribute_id' => $entityTypeTaxId, 'sort_order' => 6],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $codes = [
            'contact_type',
            'ssn',
            'date_of_birth',
            'filing_status',
            'occupation',
            'business_name',
            'ein',
            'entity_type_tax',
            'fiscal_year_end',
        ];

        // Delete attribute options for select attributes
        $attributeIds = DB::table('attributes')
            ->where('entity_type', 'persons')
            ->whereIn('code', $codes)
            ->pluck('id');

        DB::table('attribute_options')
            ->whereIn('attribute_id', $attributeIds)
            ->delete();

        // Delete the attributes themselves
        DB::table('attributes')
            ->where('entity_type', 'persons')
            ->whereIn('code', $codes)
            ->delete();
    }
};
