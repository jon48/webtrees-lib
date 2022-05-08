<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis;

use Fisharebest\Webtrees\I18N;
use Illuminate\Support\Collection;

/**
 * Result of a geographical dispersion analysis for a category.
 * An order of the categories can be defined.
 *
 */
class GeoAnalysisResult
{
    private string $description;
    private int $order;
    private int $unknown_count;
    /**
     * @var Collection<GeoAnalysisResultItem>
     */
    private Collection $places;

    /**
     * Constructor for GeoAnalysisResult
     *
     * @param string $description
     * @param int $order
     * @param Collection<GeoAnalysisResultItem> $places
     * @param int $unknown
     */
    final public function __construct(
        string $description,
        int $order = 0,
        Collection $places = null,
        int $unknown = 0
    ) {
        $this->description = $description;
        $this->order = $order;
        $this->places = $places ?? new Collection();
        $this->unknown_count = $unknown;
    }

    /**
     * Get the category description
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * Get the category order
     *
     * @return int
     */
    public function order(): int
    {
        return $this->order;
    }

    /**
     * Add a place to the analysis result
     *
     * @param GeoAnalysisPlace $place
     */
    public function addPlace(GeoAnalysisPlace $place): void
    {
        if ($place->isKnown()) {
            /** @var GeoAnalysisResultItem $item */
            $item = $this->places->get($place->key(), new GeoAnalysisResultItem($place));
            $this->places->put($item->key(), $item->increment());
        } else {
            $this->addUnknown();
        }
    }

    /**
     * Exclude a place from the analysis result
     *
     * @param GeoAnalysisPlace $place
     */
    public function exclude(GeoAnalysisPlace $place): void
    {
        /** @var GeoAnalysisResultItem|null $item */
        $item = $this->places->get($place->key());
        if ($item !== null) {
            $item->place()->exclude();
        }
    }

    /**
     * Add an unknown place to the analysis result
     */
    public function addUnknown(): void
    {
        $this->unknown_count++;
    }

    /**
     * Take a copy of the current analysis result
     *
     * @return static
     */
    public function copy(): self
    {
        return new static(
            $this->description(),
            $this->order(),
            $this->places->map(fn(GeoAnalysisResultItem $item): GeoAnalysisResultItem => clone $item),
            $this->countUnknown()
        );
    }

    /**
     * Merge the current analysis result with another.
     * The current object is modified, not the second one.
     *
     * @param GeoAnalysisResult $other
     * @return $this
     */
    public function merge(GeoAnalysisResult $other): self
    {
        $this->places->each(function (GeoAnalysisResultItem $item) use ($other): void {
            if ($other->places->has($item->key())) {
                $item->place()->exclude(
                    $item->place()->isExcluded()
                    && $other->places->get($item->key())->place()->isExcluded()
                );
            }
        });

        $other->places->each(function (GeoAnalysisResultItem $item): void {
            if (!$this->places->has($item->key())) {
                $this->addPlace($item->place());
            }
        });

        return $this;
    }

    /**
     * Get the count of Known places
     *
     * @return int
     */
    public function countKnown(): int
    {
        return $this->places->sum(fn(GeoAnalysisResultItem $item): int => $item->count()) ?? 0;
    }

    /**
     * Get the count of Found places
     *
     * @return int
     */
    public function countFound(): int
    {
        return $this->places
            ->reject(fn(GeoAnalysisResultItem $item): bool => $item->place()->isExcluded())
            ->sum(fn(GeoAnalysisResultItem $item): int => $item->count()) ?? 0;
    }

    /**
     * Get the count of Excluded places
     *
     * @return int
     */
    public function countExcluded(): int
    {
        return $this->places
            ->filter(fn(GeoAnalysisResultItem $item): bool => $item->place()->isExcluded())
            ->sum(fn(GeoAnalysisResultItem $item): int => $item->count()) ?? 0;
    }

    /**
     * Get the count of Unknown places
     *
     * @return int
     */
    public function countUnknown(): int
    {
        return $this->unknown_count;
    }

    /**
     * Get the count of the most represented Place in the analysis result
     *
     * @return int
     */
    public function maxCount(): int
    {
        return $this->places->max(fn(GeoAnalysisResultItem $item): int => $item->count()) ?? 0;
    }

    /**
     * Get the list of Known places with their associated count
     *
     * @param bool $exclude_other
     * @return Collection<GeoAnalysisResultItem>
     */
    public function knownPlaces(bool $exclude_other = false): Collection
    {
        if ($exclude_other) {
            return $this->places->reject(fn(GeoAnalysisResultItem $item): bool => $item->place()->isExcluded());
        }
        return $this->places;
    }

    /**
     * Get the list of Known places with their associated count.
     * The list is sorted first by descending count, then by ascending Place name
     *
     * @param bool $exclude_other
     * @return Collection<GeoAnalysisResultItem>
     */
    public function sortedKnownPlaces(bool $exclude_other = false): Collection
    {
        return $this->knownPlaces($exclude_other)->sortBy([
            fn (GeoAnalysisResultItem $a, GeoAnalysisResultItem $b): int => $b->count() <=> $a->count(),
            fn (GeoAnalysisResultItem $a, GeoAnalysisResultItem $b): int =>
                I18N::comparator()($a->place()->place()->gedcomName(), $b->place()->place()->gedcomName())
        ]);
    }

    /**
     * Get the list of Excluded places
     *
     * @return Collection<GeoAnalysisResultItem>
     */
    public function excludedPlaces(): Collection
    {
        return $this->places->filter(fn(GeoAnalysisResultItem $item): bool => $item->place()->isExcluded());
    }
}
