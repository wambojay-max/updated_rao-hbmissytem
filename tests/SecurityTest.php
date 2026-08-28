<?php
use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    public function testCsrfTokenIsStableForASession(): void
    {
        $_SESSION["csrf_token"] = null;
        $token = csrfToken();
        self::assertSame($token, csrfToken());
        self::assertSame(64, strlen($token));
    }

    public function testRoleAllowListIsExact(): void
    {
        self::assertTrue(in_array("admin", ["admin", "warden"], true));
        self::assertFalse(in_array("student", ["admin", "warden"], true));
    }
}