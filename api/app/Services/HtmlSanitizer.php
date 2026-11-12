<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/** Server-side allowlist sanitiser for rich text (blog bodies). Never trust the editor's output. */
class HtmlSanitizer
{
    private const ALLOWED = 'p,br,hr,strong,b,em,i,u,s,code,pre,blockquote,h2,h3,h4,ul,ol,li,'
        .'a[href|title|target],img[src|alt|width|height],table,thead,tbody,tr,th,td';

    public function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', self::ALLOWED);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);
        $config->set('Cache.DefinitionImpl', null); // no cache directory to manage; writes are rare

        return (new HTMLPurifier($config))->purify($html);
    }
}
