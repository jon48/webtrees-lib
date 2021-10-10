<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage MiscExtensions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\MiscExtensions\Hooks;

use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\NameAccordionExtenderInterface;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;

/**
 * Hook for displaying the individual's title in the names accordion.
 */
class TitlesCardHook implements NameAccordionExtenderInterface
{
    private ModuleInterface $module;

    /**
     * Constructor for TitlesCardHook
     *
     * @param ModuleInterface $module
     */
    public function __construct(ModuleInterface $module)
    {
        $this->module = $module;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookInterface::module()
     */
    public function module(): ModuleInterface
    {
        return $this->module;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\NameAccordionExtenderInterface::accordionCard()
     */
    public function accordionCard(Individual $individual): string
    {
        $title_separator = $this->module->getPreference('MAJ_TITLE_PREFIX');
        if ($title_separator === '') {
            return '';
        }

        $titles = $this->individualTitles($individual, '/(.*?) ((' . $title_separator .  ')(.*))/i');

        return count($titles) === 0 ? '' :
            view($this->module()->name() . '::components/accordion-item-titles', [ 'titles' => $titles ]);
    }

    /**
     * Extract the individual titles from the TITL tags.
     * Split the title based on a pattern to identify the title and the land it refers to.
     *
     * @param Individual $individual
     * @param string $pattern
     * @return array<string, string[]>
     */
    protected function individualTitles(Individual $individual, string $pattern): array
    {
        $titles_list = [];
        /** @var \Illuminate\Support\Collection<string> $titles */
        $titles = $individual->facts(['TITL'])
            ->sortByDesc(fn(Fact $fact) => $fact->date()->julianDay())
            ->map(fn(Fact $fact) => $fact->value());

        foreach ($titles as $title) {
            if (preg_match($pattern, $title, $match) === 1) {
                /** @var array<int, string> $match */
                $titles_list[$match[1]][] = trim($match[2]);
            } else {
                $titles_list[$title][] = '';
            }
        }
        return $titles_list;
    }
}
