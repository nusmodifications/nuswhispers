<?php

namespace NUSWhispers\Listeners;

use NUSWhispers\Models\Tag;
use Illuminate\Contracts\Queue\ShouldQueue;
use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Events\ConfessionWasUpdated;

class SyncConfessionTags implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  mixed $event
     * @return mixed
     */
    public function handle($event)
    {
        if (
            ! $event instanceof ConfessionWasCreated &&
            ! $event instanceof ConfessionWasUpdated
        ) {
            return true;
        }

        $confession = $event->confession;

        $tags = $this->fetchTagsFromContent($confession->content)
            ->map(function ($item) {
                return Tag::firstOrCreate(['confession_tag' => $item])->getKey();
            });

        $confession->tags()->sync($tags->all());
    }

    /**
     * Fetch tags from content.
     *
     * @param $content
     *
     * @return array
     */
    protected function fetchTagsFromContent($content)
    {
        preg_match_all('/(#\w+)/', $content, $matches);

        return collect(array_unique(array_shift($matches)));
    }
}
