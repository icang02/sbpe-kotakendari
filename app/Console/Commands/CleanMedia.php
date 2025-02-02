<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanMedia extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:clean-media';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Commmand untuk menghapus file di folder tiptap yang tidak digunakan.';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $posts = Post::all();
    $tiptapMedia = $posts->pluck('content')->implode(' ');
    $postImages = $posts->pluck('image')->implode(' ');

    $merge = $tiptapMedia . $postImages;

    // delete file editor
    function extractMediaUrls(string $content): array
    {
      preg_match_all('/images\/.+?\.(jpg|jpeg|png|webp|svg|pdf)/i', $content, $matches);
      return $matches[0] ?? [];
    }

    function cleanUnusedMedia(string $content): void
    {
      // Dapatkan semua URL media dari konten editor
      $usedFiles = extractMediaUrls($content);

      // Dapatkan semua file yang ada di folder `storage/public/images`
      $allFiles = Storage::disk('public')->files('images');

      foreach ($allFiles as $file) {
        // Jika file tidak ada dalam konten, hapus file
        if (!in_array($file, $usedFiles)) {
          Storage::disk('public')->delete($file);
        }
      }
    }

    cleanUnusedMedia($merge);
    $this->info('Unused media deleted successfully...');
  }
}
