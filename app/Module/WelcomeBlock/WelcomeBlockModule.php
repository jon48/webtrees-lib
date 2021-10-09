<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\WelcomeBlock;

use Aura\Router\Map;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleBlockInterface;
use Fisharebest\Webtrees\Module\ModuleBlockTrait;
use Illuminate\Support\Str;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\WelcomeBlock\Http\RequestHandlers\MatomoStats;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Welcome Block Module.
 */
class WelcomeBlockModule extends AbstractModule implements ModuleMyArtJaubInterface, ModuleBlockInterface
{
    use ModuleMyArtJaubTrait;
    use ModuleBlockTrait;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “WelcomeBlock” module */ I18N::translate('MyArtJaub Welcome Block');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “WelcomeBlock” module */ I18N::translate('The MyArtJaub Welcome block welcomes the visitor to the site, allows a quick login to the site, and displays statistics on visits.');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface::loadRoutes()
     */
    public function loadRoutes(Map $router): void
    {
        $router->attach('', '', static function (Map $router): void {

            $router->attach('', '/module-maj/welcomeblock/{block_id}', static function (Map $router): void {
                $router->tokens(['block_id' => '\d+']);
                $router->get(MatomoStats::class, '/matomostats', MatomoStats::class);
            });
        });
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return '2.0.11-v.2';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::getBlock()
     *
     * @param mixed[] $config
     */
    public function getBlock(Tree $tree, int $block_id, string $context, array $config = []): string
    {
        $fab_welcome_block_view = app(\Fisharebest\Webtrees\Module\WelcomeBlockModule::class)
            ->getBlock($tree, $block_id, ModuleBlockInterface::CONTEXT_EMBED);

        $fab_login_block_view = app(\Fisharebest\Webtrees\Module\LoginBlockModule::class)
            ->getBlock($tree, $block_id, ModuleBlockInterface::CONTEXT_EMBED);

        $content = view($this->name() . '::block-embed', [
            'block_id'                  =>  $block_id,
            'fab_welcome_block_view'    =>  $fab_welcome_block_view,
            'fab_login_block_view'      =>  $fab_login_block_view,
            'matomo_enabled'            =>  $this->isMatomoEnabled($block_id),
            'js_script_url'             =>  $this->assetUrl('js/welcomeblock.min.js')
        ]);

        if ($context !== self::CONTEXT_EMBED) {
            return view('modules/block-template', [
                'block'      => Str::kebab($this->name()),
                'id'         => $block_id,
                'config_url' => $this->configUrl($tree, $context, $block_id),
                'title'      => e($tree->title()),
                'content'    => $content,
            ]);
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::isTreeBlock()
     */
    public function isTreeBlock(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::editBlockConfiguration()
     */
    public function editBlockConfiguration(Tree $tree, int $block_id): string
    {
        return view($this->name() . '::config', $this->matomoSettings($block_id));
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::saveBlockConfiguration()
     */
    public function saveBlockConfiguration(ServerRequestInterface $request, int $block_id): void
    {
        $params = (array) $request->getParsedBody();

        $matomo_enabled = $params['matomo_enabled'] == 'yes';
        $this->setBlockSetting($block_id, 'matomo_enabled', $matomo_enabled ? 'yes' : 'no');
        if (!$matomo_enabled) {
            return;
        }

        if (filter_var($params['matomo_url'], FILTER_VALIDATE_URL) === false) {
            FlashMessages::addMessage(I18N::translate('The Matomo URL provided is not valid.'), 'danger');
            return;
        }

        if (filter_var($params['matomo_siteid'], FILTER_VALIDATE_INT) === false) {
            FlashMessages::addMessage(I18N::translate('The Matomo Site ID provided is not valid.'), 'danger');
            return;
        }

        $this
            ->setBlockSetting($block_id, 'matomo_url', trim($params['matomo_url']))
            ->setBlockSetting($block_id, 'matomo_token', trim($params['matomo_token']))
            ->setBlockSetting($block_id, 'matomo_siteid', $params['matomo_siteid']);

        Registry::cache()->file()->forget($this->name() . '-matomovisits-yearly-' . $block_id);
    }

    /**
     * Returns whether Matomo statistics is enabled for a specific MyArtJaub WelcomeBlock block
     *
     * @param int $block_id
     * @return bool
     */
    public function isMatomoEnabled(int $block_id): bool
    {
        return $this->getBlockSetting($block_id, 'matomo_enabled', 'no') === 'yes';
    }

    /**
     * Returns settings for retrieving Matomo statistics for a specific MyArtJaub WelcomeBlock block
     *
     * @param int $block_id
     * @return array<string, mixed>
     */
    public function matomoSettings(int $block_id): array
    {
        return [
            'matomo_enabled' => $this->isMatomoEnabled($block_id),
            'matomo_url' => $this->getBlockSetting($block_id, 'matomo_url'),
            'matomo_token' => $this->getBlockSetting($block_id, 'matomo_token'),
            'matomo_siteid'  => (int) $this->getBlockSetting($block_id, 'matomo_siteid', '0')
        ];
    }
}
