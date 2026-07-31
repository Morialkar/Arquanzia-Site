<?php

namespace Tests\Unit;

use App\Services\SvgSanitizer;
use PHPUnit\Framework\TestCase;

class SvgSanitizerTest extends TestCase
{
    private SvgSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new SvgSanitizer;
    }

    public function test_un_svg_inoffensif_est_conserve(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>'
        );

        $this->assertStringContainsString('<circle', $clean);
        $this->assertStringContainsString('viewBox', $clean);
    }

    public function test_une_balise_script_est_retiree(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.cookie)</script><rect/></svg>'
        );

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringContainsString('<rect', $clean);
    }

    public function test_les_attributs_d_evenement_sont_retires(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg"><rect onload="steal()" onclick="steal()" onmouseover="steal()" width="10"/></svg>'
        );

        $this->assertStringNotContainsString('onload', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringNotContainsString('steal', $clean);
        $this->assertStringContainsString('width="10"', $clean);
    }

    /** Tout attribut commençant par « on » saute, y compris ceux hors de la liste. */
    public function test_un_attribut_d_evenement_non_liste_est_retire(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg"><rect onanimationstart="steal()"/></svg>'
        );

        $this->assertStringNotContainsString('onanimationstart', $clean);
    }

    public function test_foreign_object_est_retire(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg"><foreignObject><body xmlns="http://www.w3.org/1999/xhtml">html</body></foreignObject></svg>'
        );

        $this->assertStringNotContainsString('foreignObject', $clean);
        $this->assertStringNotContainsString('html', $clean);
    }

    public function test_une_url_javascript_est_retiree(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg"><a href="javascript:alert(1)"><rect/></a></svg>'
        );

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('<rect', $clean);
    }

    public function test_un_doctype_avec_entites_est_retire(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
            .'<svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>'
        );

        $this->assertStringNotContainsString('DOCTYPE', $clean);
        $this->assertStringNotContainsString('ENTITY', $clean);
        $this->assertStringNotContainsString('passwd', $clean);
    }

    public function test_un_contenu_qui_n_est_pas_un_svg_est_refuse(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->sanitizer->sanitize('<html><body>pas un svg</body></html>');
    }

    public function test_un_contenu_vide_est_refuse(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->sanitizer->sanitize('   ');
    }
}
