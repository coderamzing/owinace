<?php

namespace App\Filament\Resources\Contacts;

use App\Filament\Resources\Contacts\Pages\ListContacts;
use App\Filament\Resources\Contacts\Schemas\ContactForm;
use App\Filament\Resources\Contacts\Tables\ContactsTable;
use App\Models\Contact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Traits\HasPermission;

class ContactResource extends Resource
{
    use HasPermission;
    
    protected static ?string $permission = 'contact.list';
    
    protected static ?string $model = Contact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
            'import' => \App\Filament\Resources\Contacts\Pages\ImportContacts::route('/import'),
        ];
    }
}
