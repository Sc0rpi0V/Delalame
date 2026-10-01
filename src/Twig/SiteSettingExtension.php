<?php

namespace App\Twig;

use App\Repository\SiteContentRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class SiteSettingExtension extends AbstractExtension implements GlobalsInterface
{
    private ?array $settings = null;

    public function __construct(private SiteContentRepository $repo) {}

    public function getGlobals(): array
    {
        if ($this->settings === null) {
            $this->settings = [];
            foreach ($this->repo->findAll() as $item) {
                $this->settings[$item->getContentKey()] = $item->getContent();
            }
        }

        return ['site_settings' => $this->settings];
    }
}
