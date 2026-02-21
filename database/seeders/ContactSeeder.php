<?php

namespace Database\Seeders;

use App\Enums\ContactRole;
use App\Models\Contact;
use App\Models\Property;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $eliene = Contact::firstOrCreate(
            ['phone' => '+56999834369'],
            [
                'name' => 'Eliene',
                'role' => ContactRole::Cleaning,
            ]
        );

        $viviana = Contact::firstOrCreate(
            ['phone' => '+56973978287'],
            [
                'name' => 'Viviana Quintomán',
                'role' => ContactRole::Cleaning,
            ]
        );

        Contact::firstOrCreate(
            ['phone' => '+56944374529'],
            [
                'name' => 'Guillermo',
                'role' => ContactRole::Handyman,
            ]
        );

        Contact::firstOrCreate(
            ['phone' => '+56986971605'],
            [
                'name' => 'Peña',
                'role' => ContactRole::Handyman,
            ]
        );

        Property::where('slug', 'casa-pupuya')->update(['cleaning_contact_id' => $eliene->id]);
        Property::where('slug', 'cabana-pullinque')->update(['cleaning_contact_id' => $viviana->id]);
    }
}
