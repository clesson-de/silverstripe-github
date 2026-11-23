<?php

use Clesson\Silverstripe\GitHub\Models\GitHubRepository;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\TinyMCE\TinyMCEConfig;
use TractorCow\Fluent\Extension\FluentExtension;

// Register custom HTMLEditorConfig for the GitHubRepository PromotionDescription field.
call_user_func(function () {
    $cmsConfig = HTMLEditorConfig::get('cms');

    if (!$cmsConfig instanceof TinyMCEConfig) {
        return;
    }

    $config = clone $cmsConfig;
    $config->setOption('friendly_name', 'GitHub Repository — Promotion Description');
    $config->removeButtons('ssmedia', 'ssembed', 'anchor');
    $config->disablePlugins('ssmedia', 'ssembed', 'sslinkanchor');
    $config->setButtonsForLine(1, [
        'bold', 'italic', 'underline', 'removeformat', '|',
        'alignleft', 'aligncenter', 'alignright', '|',
        'bullist', 'numlist', 'outdent', 'indent', '|',
        'sslink', 'unlink', '|',
        'code',
    ]);
    $config->setButtonsForLine(2, []);
    $config->setButtonsForLine(3, []);

    HTMLEditorConfig::set_config('GitHubRepository_PromotionDescription_EditorConfig', $config);
    HTMLEditorConfig::set_config('GitHubRepository_PromotionDescription_EditorConfig', $config);
});
if (class_exists(FluentExtension::class)) {
    Config::modify()->merge(GitHubRepository::class, 'extensions', [
        FluentExtension::class,
    ]);
    Config::modify()->merge(GitHubRepository::class, 'translate', [
        'PromotionTitle',
        'PromotionDescription',
    ]);
}
