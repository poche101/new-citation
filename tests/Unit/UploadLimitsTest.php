<?php

namespace Tests\Unit;

use App\Providers\AppServiceProvider;
use PHPUnit\Framework\TestCase;

class UploadLimitsTest extends TestCase
{
    private function kb(string $value): int
    {
        $provider = (new \ReflectionClass(AppServiceProvider::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($provider, 'iniToKb');

        return $method->invoke($provider, $value);
    }

    public function test_php_size_strings_convert_to_kilobytes(): void
    {
        $this->assertSame(2048, $this->kb('2M'));
        $this->assertSame(614400, $this->kb('600M'));
        $this->assertSame(2097152, $this->kb('2G'));
        $this->assertSame(800, $this->kb('800K'));
        $this->assertSame(8192, $this->kb('8388608'));
        $this->assertSame(0, $this->kb('0'));
        $this->assertSame(0, $this->kb('-1'));
        $this->assertSame(0, $this->kb(''));
    }
}
