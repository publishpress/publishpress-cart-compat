<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use Tests\Support\WordPressStubContext;
use UnitTester;

class DownloadUploadDirectoryTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var string
     */
    private $tempRoot;

    /**
     * @var string
     */
    private $uploadBase;

    protected function _before(): void
    {
        $this->tempRoot = sys_get_temp_dir() . '/ppcart-compat-uploads-' . uniqid('', true);
        $this->uploadBase = $this->tempRoot . '/wp-content/uploads';
        wp_mkdir_p($this->uploadBase);

        if (function_exists('ppcart_unregister_legacy_download_upload_roots')) {
            ppcart_unregister_legacy_download_upload_roots();
        }

        WordPressStubContext::clear();
        WordPressStubContext::set(
            'wp_upload_dir',
            function () {
                return [
                    'basedir' => $this->uploadBase,
                    'baseurl' => 'https://example.test/wp-content/uploads',
                    'error'   => false,
                ];
            }
        );
        WordPressStubContext::set(
            'has_filter',
            static function ($hook, $callback = false) {
                return false;
            }
        );
        WordPressStubContext::set(
            'add_filter',
            static function ($hook, $callback, $priority = 10, $accepted_args = 1) {
                return true;
            }
        );
        WordPressStubContext::set(
            'remove_filter',
            static function ($hook, $callback, $priority = 10) {
                return true;
            }
        );
    }

    protected function _after(): void
    {
        if (function_exists('ppcart_unregister_legacy_download_upload_roots')) {
            ppcart_unregister_legacy_download_upload_roots();
        }

        $this->removeDirectory($this->tempRoot);
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-308
     */
    public function test_UT_308_legacy_download_upload_map_lists_leftover_sc_uploads(): void
    {
        $map = ncs_cart_get_legacy_download_upload_directory_map();

        $this->assertSame([ 'sc-uploads' ], $map['ppcart-uploads']);
        $this->assertSame('sc-uploads', ppcart_legacy_download_upload_directory_basename());
    }

    /**
     * @test-id UT-308
     */
    public function test_UT_308_extra_root_appends_leftover_sc_uploads_when_present(): void
    {
        $canonical = realpath($this->uploadBase);
        $this->assertNotFalse($canonical);

        $withoutDir = ppcart_add_legacy_download_upload_root([ $canonical ]);
        $this->assertSame([ $canonical ], $withoutDir);

        $leftoverDir = $this->uploadBase . '/sc-uploads';
        wp_mkdir_p($leftoverDir);
        file_put_contents($leftoverDir . '/legacy.zip', 'legacy');

        $withDir = ppcart_add_legacy_download_upload_root([ $canonical ]);
        $leftoverReal = realpath($leftoverDir);
        $this->assertNotFalse($leftoverReal);
        $this->assertContains($leftoverReal, $withDir);
        $this->assertContains($canonical, $withDir);
        $this->assertFileExists($leftoverDir . '/legacy.zip');
        $this->assertFileDoesNotExist($this->uploadBase . '/ppcart-uploads/legacy.zip');
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->removeDirectory($path);
                continue;
            }

            @unlink($path);
        }

        @rmdir($directory);
    }
}
