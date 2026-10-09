<?php

namespace Tests\Unit;

use App\Http\Controllers\MediaController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class MediaControllerSecurityTest extends TestCase
{
    public function test_parent_directory_segments_are_rejected(): void
    {
        $this->expectException(NotFoundHttpException::class);

        (new MediaController)->show('uploads/../../.env');
    }
}
