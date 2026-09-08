<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Files;
use PPCartComp_Studiocart_Compatibility_Mode;

class DownloadUploadDirectoryTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var string
     */
    private $leftoverDir = '';

    /**
     * @var string
     */
    private $leftoverFile = '';

    /**
     * @var bool
     */
    private $createdLeftoverDir = false;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_download_upload_roots')) {
            ppcart_unregister_legacy_download_upload_roots();
        }

        $upload = wp_upload_dir();
        $this->leftoverDir = $upload['basedir'] . '/sc-uploads';
        $this->leftoverFile = $this->leftoverDir . '/ppcart-it-361-legacy.zip';
        $this->createdLeftoverDir = ! is_dir($this->leftoverDir);

        wp_mkdir_p($this->leftoverDir);
        file_put_contents($this->leftoverFile, 'legacy-download');
    }

    protected function tearDown(): void
    {
        if ('' !== $this->leftoverFile && file_exists($this->leftoverFile)) {
            @unlink($this->leftoverFile);
        }

        if ($this->createdLeftoverDir && is_dir($this->leftoverDir)) {
            @rmdir($this->leftoverDir);
        }

        if (function_exists('ppcart_unregister_legacy_download_upload_roots')) {
            ppcart_unregister_legacy_download_upload_roots();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-361
     */
    public function test_IT_361_leftover_sc_uploads_root_allowed_only_when_compatibility_mode_is_on(): void
    {
        $leftoverReal = realpath($this->leftoverDir);
        $this->assertNotFalse($leftoverReal);
        $this->assertFileExists($this->leftoverFile);

        $rootsOff = PPCart_Files::get_allowed_download_roots();
        $this->assertNotContains($leftoverReal, $rootsOff);
        $this->assertFalse(PPCart_Files::resolve_local_download_path($this->leftoverFile));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_download_upload_directory();

        $rootsOn = PPCart_Files::get_allowed_download_roots();
        $this->assertContains($leftoverReal, $rootsOn);
        $this->assertSame(
            realpath($this->leftoverFile),
            PPCart_Files::resolve_local_download_path($this->leftoverFile)
        );

        $upload = wp_upload_dir();
        $this->assertFileDoesNotExist($upload['basedir'] . '/ppcart-uploads/ppcart-it-361-legacy.zip');
        $this->assertFileExists($this->leftoverFile);
    }
}
