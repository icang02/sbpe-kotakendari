<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostResource extends Resource
{
  protected static ?string $model = Post::class;

  // protected static ?string $navigationGroup = 'Manajemen Artikel';

  protected static ?string $navigationIcon  = 'heroicon-o-newspaper';
  protected static ?string $navigationLabel = 'Postingan';

  public static function beforeSave($record)
  {
    $record->content = preg_replace('/\s+/', ' ', $record->content);
    $record->title   = ucfirst($record->title);
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Grid::make()
          ->columns([
            'default' => 1,
            'lg' => 3,
          ])
          ->schema([
            Forms\Components\Card::make()
              ->schema([
                // Forms\Components\Select::make('categories')
                //   ->label('Pilih Kategori')
                //   ->relationship('categories', 'name')
                //   ->multiple()
                //   ->preload()
                //   ->required(),
                // Judul dan slug
                Forms\Components\TextInput::make('title')
                  ->label('Judul')
                  ->placeholder('Judul')
                  ->required()
                  ->live()
                  ->debounce(500)
                  ->dehydrateStateUsing(fn($state) => ucfirst($state))
                  ->afterStateUpdated(function (Set $set, ?string $state) {
                    $slug = str()->slug($state);
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Post::where('slug', $slug)->exists()) {
                      $slug = $originalSlug . '-' . $counter;
                      $counter++;
                    }
                    $set('slug', $slug);
                  }),
                Forms\Components\TextInput::make('slug')
                  ->label('Slug')
                  ->helperText('Terisi otomatis setelah mengisi judul.')
                  ->placeholder('Slug')
                  ->unique(ignoreRecord: true)
                  ->required(),
                // Editor konten
                TiptapEditor::make('content')
                  ->profile('tiptap')
                  ->required()
                  ->maxSize(1024)
                  ->disk('public')
                  ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'application/pdf'])
                  ->extraInputAttributes(['style' => 'min-height: 10rem; max-height: 30rem;']),
              ])
              ->columnSpan(['default' => 1, 'lg' => 2]),

            Forms\Components\Grid::make()
              ->schema([
                Forms\Components\Card::make()
                  ->schema([
                    Forms\Components\FileUpload::make('image')
                      ->label('Cover')
                      ->image()
                      ->maxSize(1024)
                      ->directory('images')
                      ->imageEditor()
                      ->disk('public')
                      ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                        $date = now()->format('Y-m-d');
                        $time = now()->format('H:i:s') . ':' . now()->format('v');
                        $originalName = $file->getClientOriginalName();
                        return $date . '-at-' . $time . '-' . $originalName;
                      }),
                  ]),
                Forms\Components\Card::make()
                  ->schema([
                    Forms\Components\DateTimePicker::make('created_at')
                      ->label('Tanggal Post')
                      ->default(\Carbon\Carbon::now())
                      ->required(),
                  ])
              ])->columnSpan(['default' => 1, 'lg' => 1])

          ])
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->query(
        static::getModel()::query()->orderBy('created_at', 'desc')
      )
      ->columns([
        Tables\Columns\TextColumn::make('title')->label('Judul')
          ->searchable()->sortable()
          ->getStateUsing(function ($record) {
            return str()->words($record->title, 10);
          }),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Tanggal Post')
          ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format("d M Y - H:i:s"))
          ->sortable()
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make()
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
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
      'index' => Pages\ListPosts::route('/'),
      'create' => Pages\CreatePost::route('/create'),
      'edit' => Pages\EditPost::route('/{record}/edit'),
    ];
  }
}
