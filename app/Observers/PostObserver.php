<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
  /**
   * Handle the Post "created" event.
   */
  public function created(Post $post): void
  {
    //
  }

  /**
   * Handle the Post "updated" event.
   */
  public function updated(Post $post): void
  {
    if ($post->isDirty('image') && $post->image != null) {
      Storage::disk('public')->delete($post->getOriginal('image'));
    }
  }

  /**
   * Handle the Post "deleted" event.
   */
  public function deleted(Post $post): void
  {
    if (!is_null($post->image)) {
      Storage::disk('public')->delete($post->image);
    }

    // Clean unused media
    Artisan::call('app:clean-media');
  }

  /**
   * Handle the Post "restored" event.
   */
  public function restored(Post $post): void
  {
    //
  }

  /**
   * Handle the Post "force deleted" event.
   */
  public function forceDeleted(Post $post): void
  {
    //
  }
}
