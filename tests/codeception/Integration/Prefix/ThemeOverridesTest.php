<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class ThemeOverridesTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var list<string>
     */
    private $createdFiles = [];

    /**
     * @var list<string>
     */
    private $createdDirs = [];

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        foreach (array_reverse($this->createdDirs) as $dir) {
            if (is_dir($dir)) {
                @rmdir($dir);
            }
        }

        $this->createdFiles = [];
        $this->createdDirs = [];

        parent::tearDown();
    }

    /**
     * @test-id IT-342
     */
    public function test_IT_342_leftover_theme_overrides_resolve_only_when_compatibility_mode_is_on(): void
    {
        $relative = 'it-342/marker.php';
        $leftover = $this->writeThemeFile('studiocart', $relative, '<?php // leftover');

        $off = ppcart_get_template_path('it-342/marker');
        $this->assertFalse($off);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $on = ppcart_get_template_path('it-342/marker');
        $this->assertSame($leftover, $on);

        $canonical = $this->writeThemeFile('publishpress-cart', $relative, '<?php // canonical');
        $both = ppcart_get_template_path('it-342/marker');
        $this->assertSame($canonical, $both);
        $this->assertFileExists($leftover);
    }

    /**
     * @param string $directory Theme subdirectory name.
     * @param string $relative  Path under that subdirectory.
     * @param string $contents  File contents.
     * @return string Absolute path written.
     */
    private function writeThemeFile($directory, $relative, $contents)
    {
        $path = get_stylesheet_directory() . '/' . $directory . '/' . $relative;
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->trackDir($dir);
        $this->trackDir(dirname($dir));

        file_put_contents($path, $contents);
        $this->createdFiles[] = $path;

        return $path;
    }

    /**
     * @param string $dir Directory to remove after the test.
     * @return void
     */
    private function trackDir($dir)
    {
        $stylesheet = rtrim(get_stylesheet_directory(), '/\\');
        $dir        = rtrim($dir, '/\\');

        if ($dir === $stylesheet || 0 !== strpos($dir, $stylesheet . '/')) {
            return;
        }

        if (! in_array($dir, $this->createdDirs, true)) {
            $this->createdDirs[] = $dir;
        }
    }
}
