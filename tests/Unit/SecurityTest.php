<?php
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase {

    public function testSanitizeRemovesTagsAndTrims() {
        $input = "   <script>alert('xss');</script> Safe Text   ";
        $expected = "alert('xss'); Safe Text";
        $this->assertEquals($expected, Security::sanitize($input));
    }

    public function testEscapeConvertsHtmlEntities() {
        $input = "<div class='test'>Test & Co</div>";
        $expected = "&lt;div class=&#039;test&#039;&gt;Test &amp; Co&lt;/div&gt;";
        $this->assertEquals($expected, Security::escape($input));
    }

    public function testValidateServiceNumberAllowsDigits() {
        $this->assertTrue(Security::validateServiceNumber('12345'));
        $this->assertTrue(Security::validateServiceNumber('admin'));
        $this->assertTrue(Security::validateServiceNumber('sadmin'));
        $this->assertTrue(Security::validateServiceNumber('ADMIN'));
        $this->assertTrue(Security::validateServiceNumber('AW/5188'));
        $this->assertTrue(Security::validateServiceNumber('V/2311'));
        $this->assertTrue(Security::validateServiceNumber('SLAF/AIR/301'));
        
        $this->assertFalse(Security::validateServiceNumber('invalid_user'));
        $this->assertFalse(Security::validateServiceNumber('AW-5188'));
        $this->assertFalse(Security::validateServiceNumber('AW@5188'));
        $this->assertFalse(Security::validateServiceNumber('123456789012345678901'));
    }
}
