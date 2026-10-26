<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LicenseViewComposerTest extends TestCase
{
    public function test_license_status_is_available_to_configuration_view(): void
    {
        $view = View::make('panel.config');

        View::callComposer($view);

        $this->assertArrayHasKey('applicationLicense', $view->getData());
        $this->assertArrayHasKey('state', $view->getData()['applicationLicense']);
    }
}
